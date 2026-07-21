<?php
/**
 * Minimal MercadoPago Checkout Pro client.
 *
 * Security model:
 *  - mp_create_preference() only ever sends data TO MercadoPago (order total,
 *    items, back_urls). It never receives a trusted "paid" signal.
 *  - The ONLY function that may mark an order as paid is mp_get_payment(),
 *    which performs an authenticated server-to-server GET against MercadoPago's
 *    API using our secret access token. Nothing from the browser (redirect
 *    query params, POSTed form fields, webhook body) is ever trusted directly —
 *    every payment confirmation is re-derived from this call. See webhook/mercadopago.php.
 */

function mp_configured(): bool
{
    return MERCADOPAGO_ACCESS_TOKEN !== '';
}

function mp_request(string $method, string $path, ?array $body = null): array
{
    $ch = curl_init('https://api.mercadopago.com' . $path);
    $headers = [
        'Authorization: Bearer ' . MERCADOPAGO_ACCESS_TOKEN,
        'Content-Type: application/json',
    ];
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'error' => 'curl_error: ' . $curlErr, 'http_code' => 0, 'data' => null];
    }
    $decoded = json_decode($raw, true);
    return [
        'ok' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'data' => $decoded,
        'error' => ($httpCode >= 200 && $httpCode < 300) ? null : ($decoded['message'] ?? 'HTTP ' . $httpCode),
    ];
}

/**
 * Creates a MercadoPago "preference" (a hosted checkout session) for an order.
 * Returns ['ok' => bool, 'init_point' => ?string, 'preference_id' => ?string, 'error' => ?string].
 */
function mp_create_preference(int $orderId, float $total, array $itemLines, ?string $payerEmail, ?string $payerName): array
{
    if (!mp_configured()) {
        return ['ok' => false, 'error' => 'MercadoPago is not configured (MERCADOPAGO_ACCESS_TOKEN is empty).'];
    }

    $items = [];
    foreach ($itemLines as $line) {
        $items[] = [
            'title' => $line['name'],
            'quantity' => (int) $line['quantity'],
            'unit_price' => (float) $line['unit_price'],
            'currency_id' => 'UYU',
        ];
    }

    $payload = [
        'items' => $items,
        'external_reference' => (string) $orderId,
        'notification_url' => SITE_URL . '/webhook/mercadopago.php',
        'back_urls' => [
            'success' => SITE_URL . '/order-confirmation.php?id=' . $orderId,
            'pending' => SITE_URL . '/order-confirmation.php?id=' . $orderId,
            'failure' => SITE_URL . '/order-confirmation.php?id=' . $orderId,
        ],
        'auto_return' => 'approved',
        'statement_descriptor' => 'FRANCA',
    ];
    if ($payerEmail) {
        $payload['payer'] = array_filter(['email' => $payerEmail, 'name' => $payerName]);
    }

    $result = mp_request('POST', '/checkout/preferences', $payload);
    if (!$result['ok']) {
        return ['ok' => false, 'error' => $result['error'] ?? 'Unknown MercadoPago error'];
    }

    return [
        'ok' => true,
        'preference_id' => $result['data']['id'] ?? null,
        'init_point' => $result['data']['init_point'] ?? null,
    ];
}

/**
 * Authoritative payment lookup. Always call this instead of trusting any
 * payment status supplied by the caller (redirect query string or webhook body).
 */
function mp_get_payment(string $paymentId): array
{
    if (!mp_configured()) {
        return ['ok' => false, 'error' => 'MercadoPago is not configured.'];
    }
    $result = mp_request('GET', '/v1/payments/' . urlencode($paymentId));
    if (!$result['ok']) {
        return ['ok' => false, 'error' => $result['error'] ?? 'Unknown MercadoPago error'];
    }
    return ['ok' => true, 'payment' => $result['data']];
}
