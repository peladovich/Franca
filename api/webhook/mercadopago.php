<?php
/**
 * MercadoPago webhook (IPN) receiver.
 *
 * This is a PUBLIC endpoint — anyone on the internet can POST to it, including
 * attackers. That is fine, because nothing it receives is trusted directly:
 *
 *   1. We extract only a payment_id from the request (never a status/amount).
 *   2. We re-fetch that payment_id from MercadoPago's API ourselves, using our
 *      secret access token (mp_get_payment). Only MercadoPago's own answer to
 *      that authenticated call is trusted.
 *   3. We cross-check the re-fetched payment's external_reference (our order
 *      id) and transaction_amount against what we stored when the order was
 *      created, before ever marking anything paid.
 *   4. Every call is logged to payment_events for audit, whether or not it
 *      resulted in a state change.
 *
 * An attacker spamming this endpoint with made-up payment IDs achieves nothing:
 * step 2 will simply fail to find a matching real payment, or return a payment
 * that doesn't belong to this order/amount, and nothing gets marked paid.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/payments/mercadopago.php';

function respond(int $code, string $message = 'ok'): void
{
    http_response_code($code);
    header('Content-Type: text/plain');
    echo $message;
    exit;
}

function log_event(?int $orderId, ?string $paymentId, string $rawPayload, ?string $verifiedStatus, ?float $verifiedAmount, string $outcome): void
{
    $stmt = db()->prepare(
        "INSERT INTO payment_events (order_id, provider, payment_id, raw_payload, verified_status, verified_amount, outcome)
         VALUES (?, 'mercadopago', ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$orderId, $paymentId, substr($rawPayload, 0, 60000), $verifiedStatus, $verifiedAmount, $outcome]);
}

$rawBody = file_get_contents('php://input') ?: '';
$jsonBody = json_decode($rawBody, true);

// MercadoPago sends notifications either as query params (legacy IPN) or a
// JSON POST body (current Webhooks format). Support both.
$paymentId = $_GET['data_id']
    ?? $_GET['id']
    ?? ($jsonBody['data']['id'] ?? null);
$topic = $_GET['type'] ?? $_GET['topic'] ?? ($jsonBody['type'] ?? null);

if (!$paymentId || ($topic !== null && $topic !== 'payment')) {
    // Not a payment notification (e.g. merchant_order topic) — acknowledge and ignore.
    log_event(null, is_string($paymentId) ? $paymentId : null, $rawBody, null, null, 'ignored_non_payment_topic');
    respond(200, 'ignored');
}

$paymentId = (string) $paymentId;
$lookup = mp_get_payment($paymentId);

if (!$lookup['ok']) {
    log_event(null, $paymentId, $rawBody, null, null, 'lookup_failed: ' . ($lookup['error'] ?? 'unknown'));
    // Ask MercadoPago to retry later rather than silently dropping a real event.
    respond(502, 'lookup failed');
}

$payment = $lookup['payment'];
$verifiedStatus = $payment['status'] ?? null;
$verifiedAmount = isset($payment['transaction_amount']) ? (float) $payment['transaction_amount'] : null;
$orderId = isset($payment['external_reference']) ? (int) $payment['external_reference'] : null;

if (!$orderId) {
    log_event(null, $paymentId, $rawBody, $verifiedStatus, $verifiedAmount, 'no_external_reference');
    respond(200, 'no order reference');
}

$orderStmt = db()->prepare('SELECT * FROM orders WHERE id = ?');
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch();

if (!$order) {
    log_event($orderId, $paymentId, $rawBody, $verifiedStatus, $verifiedAmount, 'order_not_found');
    respond(200, 'order not found');
}

if ($order['payment_status'] === 'paid') {
    // Already processed (MercadoPago retries notifications) — idempotent no-op.
    log_event($orderId, $paymentId, $rawBody, $verifiedStatus, $verifiedAmount, 'already_paid_noop');
    respond(200, 'already processed');
}

$amountMatches = $verifiedAmount !== null && abs($verifiedAmount - (float) $order['total']) < 1.0;

if ($verifiedStatus === 'approved' && $amountMatches) {
    $update = db()->prepare(
        "UPDATE orders SET payment_status = 'paid', payment_reference = ?, payment_amount = ?, payment_confirmed_at = NOW() WHERE id = ?"
    );
    $update->execute([$paymentId, $verifiedAmount, $orderId]);
    log_event($orderId, $paymentId, $rawBody, $verifiedStatus, $verifiedAmount, 'marked_paid');
    respond(200, 'paid');
}

if ($verifiedStatus === 'approved' && !$amountMatches) {
    // Genuine approved payment, but the amount doesn't match this order —
    // do NOT mark it paid. Flag it for manual review instead.
    log_event($orderId, $paymentId, $rawBody, $verifiedStatus, $verifiedAmount, 'amount_mismatch_flagged');
    respond(200, 'amount mismatch');
}

if (in_array($verifiedStatus, ['rejected', 'cancelled'], true)) {
    db()->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ? AND payment_status = 'pending'")->execute([$orderId]);
    log_event($orderId, $paymentId, $rawBody, $verifiedStatus, $verifiedAmount, 'marked_failed');
    respond(200, 'failed');
}

// pending / in_process / other — no state change yet, just log.
log_event($orderId, $paymentId, $rawBody, $verifiedStatus, $verifiedAmount, 'status_' . $verifiedStatus);
respond(200, 'noted');
