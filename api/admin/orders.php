<?php
$pageTitle = 'Orders | Franca Admin';
$active = 'orders';
require __DIR__ . '/includes/bootstrap.php';

$db = db();
$validStatuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify() && ($_POST['action'] ?? '') === 'update_status') {
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $orderRow = $db->prepare("SELECT payment_status FROM orders WHERE id = ?");
    $orderRow->execute([$id]);
    $paymentStatus = $orderRow->fetchColumn();

    // Server-side enforcement: an order can only move into the kitchen workflow
    // (preparing/ready/completed) once payment_status is 'paid'. 'cancelled' is
    // always allowed (e.g. to void an order that never got paid).
    if (!in_array($status, $validStatuses, true)) {
        flash('error', t('admin.invalid_status'));
    } elseif ($status !== 'pending' && $status !== 'cancelled' && $paymentStatus !== 'paid') {
        flash('error', t('admin.order_not_paid'));
    } else {
        $db->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $id]);
        flash('success', t('admin.order_status_updated'));
    }
    header('Location: ' . BASE_URL . '/admin/orders.php');
    exit;
}

$orders = $db->query("SELECT o.*, u.name AS user_name FROM orders o LEFT JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC")->fetchAll();

$itemsStmt = $db->prepare("SELECT oi.*, mi.name FROM order_items oi JOIN menu_items mi ON mi.id = oi.menu_item_id WHERE oi.order_id = ?");

$statusColors = [
    'pending' => 'bg-secondary-container text-on-secondary-container',
    'preparing' => 'bg-secondary-container text-on-secondary-container',
    'ready' => 'bg-secondary text-on-secondary',
    'completed' => 'bg-surface-container-high text-on-surface-variant',
    'cancelled' => 'bg-error-container text-on-error-container',
];

$paymentColors = [
    'pending' => 'bg-error-container text-on-error-container',
    'paid' => 'bg-secondary text-on-secondary',
    'failed' => 'bg-error-container text-on-error-container',
    'cancelled' => 'bg-surface-container-high text-on-surface-variant',
];

require __DIR__ . '/includes/layout_head.php';
?>

<h1 class="font-headline-md text-headline-md text-primary mb-lg"><?= e(t('admin.orders_title')) ?></h1>

<div class="space-y-3">
  <?php foreach ($orders as $o):
    $itemsStmt->execute([$o['id']]);
    $lines = $itemsStmt->fetchAll();
  ?>
  <details class="bg-surface-container-lowest rounded-xl editorial-shadow">
    <summary class="p-md flex flex-wrap justify-between items-center gap-3 cursor-pointer list-none">
      <div>
        <p class="font-label-md text-primary"><?= e(t('admin.order_number')) ?><?= (int)$o['id'] ?> · <?= e($o['user_name'] ?: $o['guest_name'] ?: t('admin.guest')) ?></p>
        <p class="font-caption text-on-surface-variant"><?= e(date('M j, Y g:i A', strtotime($o['created_at']))) ?> · <?= e(service_mode_label($o['service_mode'])) ?></p>
      </div>
      <div class="flex items-center gap-3">
        <span class="font-label-md text-accent-dark"><?= money($o['total']) ?></span>
        <span class="px-2 py-0.5 rounded-full text-[11px] font-label-md uppercase <?= $paymentColors[$o['payment_status']] ?? '' ?>"><?= $o['payment_status'] === 'paid' ? e(t('admin.paid')) : e(t('admin.payment_label')) . e(status_label($o['payment_status'])) ?></span>
        <span class="px-2 py-0.5 rounded-full text-[11px] font-label-md uppercase <?= $statusColors[$o['status']] ?? '' ?>"><?= e(status_label($o['status'])) ?></span>
      </div>
    </summary>
    <div class="border-t border-outline-variant/20 p-md">
      <?php if ($o['payment_status'] === 'paid'): ?>
        <p class="font-caption text-on-surface-variant mb-3"><?= e(t('admin.paid_via')) ?> <?= e($o['payment_provider'] ?: 'unknown') ?> · <?= e(t('admin.ref')) ?> <?= e($o['payment_reference'] ?: '—') ?><?= $o['payment_confirmed_at'] ? ' · ' . e(t('admin.confirmed_at')) . ' ' . e(date('M j, g:i A', strtotime($o['payment_confirmed_at']))) : '' ?></p>
      <?php else: ?>
        <p class="font-caption text-error mb-3"><?= e(t('admin.not_paid_yet')) ?></p>
      <?php endif; ?>
      <ul class="mb-3 space-y-1">
        <?php foreach ($lines as $l): ?>
          <li class="font-body-md text-sm flex justify-between"><span><?= (int)$l['quantity'] ?> × <?= e($l['name']) ?></span><span><?= money($l['unit_price'] * $l['quantity']) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <form method="post" class="flex flex-wrap gap-2 items-center">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
        <select name="status" class="bg-background border border-outline-variant/50 rounded-lg px-3 py-2 font-body-md text-sm">
          <?php foreach ($validStatuses as $s): ?>
            <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?> <?= ($s !== 'pending' && $s !== 'cancelled' && $o['payment_status'] !== 'paid') ? 'disabled' : '' ?>><?= e(status_label($s)) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="bg-accent text-on-accent px-4 py-2 rounded-full font-label-md text-sm" type="submit"><?= e(t('admin.update')) ?></button>
      </form>
    </div>
  </details>
  <?php endforeach; ?>
  <?php if (!$orders): ?><p class="font-body-md text-on-surface-variant"><?= e(t('admin.no_orders_yet')) ?></p><?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
