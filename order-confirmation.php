<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$itemsStmt = db()->prepare("SELECT oi.*, mi.name, mi.name_en, mi.image FROM order_items oi JOIN menu_items mi ON mi.id = oi.menu_item_id WHERE oi.order_id = ?");
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

// This page is reached three ways: MercadoPago's back_urls redirect here
// after checkout, someone revisits the link later, or an admin peeks at it.
// The redirect itself proves nothing about payment — MercadoPago's webhook
// (webhook/mercadopago.php) is the only thing that ever sets payment_status
// to 'paid', so that's the only thing this page trusts.
$paymentStatus = $order['payment_status'];

$pageTitle = ($paymentStatus === 'paid' ? t('order.title_confirmed') : t('order.title_status')) . ' | Franca';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<div class="max-w-lg mx-auto text-center py-lg">
<?php if ($paymentStatus === 'paid'): ?>
  <span class="material-symbols-outlined text-6xl text-secondary mb-md" style="font-variation-settings: 'FILL' 1;">check_circle</span>
  <h1 class="font-headline-md text-headline-md text-primary mb-2"><?= e(t('order.thank_you', ['name' => $order['guest_name'] ? ', ' . $order['guest_name'] : ''])) ?></h1>
  <p class="font-body-md text-on-surface-variant mb-lg"><?= e(t('order.paid_message', ['id' => (int)$order['id'], 'mode' => service_mode_label($order['service_mode'])])) ?></p>
<?php elseif ($paymentStatus === 'failed' || $paymentStatus === 'cancelled'): ?>
  <span class="material-symbols-outlined text-6xl text-error mb-md">error</span>
  <h1 class="font-headline-md text-headline-md text-primary mb-2"><?= e(t('order.not_completed_title')) ?></h1>
  <p class="font-body-md text-on-surface-variant mb-lg"><?= e(t('order.not_completed_message', ['id' => (int)$order['id']])) ?></p>
<?php else: ?>
  <span class="material-symbols-outlined text-6xl text-secondary mb-md">hourglass_top</span>
  <h1 class="font-headline-md text-headline-md text-primary mb-2"><?= t('order.confirming_title') ?></h1>
  <p class="font-body-md text-on-surface-variant mb-lg"><?= e(t('order.confirming_message', ['id' => (int)$order['id']])) ?></p>
  <meta http-equiv="refresh" content="5">
<?php endif; ?>

  <div class="bg-surface-container-lowest rounded-xl p-md text-left space-y-3 mb-lg">
    <?php foreach ($items as $it): ?>
    <div class="flex justify-between font-body-md">
      <span><?= (int)$it['quantity'] ?> × <?= e(mi_field($it, 'name')) ?></span>
      <span><?= money($it['unit_price'] * $it['quantity']) ?></span>
    </div>
    <?php endforeach; ?>
    <div class="flex justify-between font-headline-sm text-headline-sm text-primary pt-2 border-t border-outline-variant/30">
      <span><?= e(t('order.total')) ?></span><span><?= money($order['total']) ?></span>
    </div>
  </div>

  <a href="<?= BASE_URL ?>/menu.php" class="btn-lift bg-primary text-on-primary px-md py-3 rounded-lg font-label-md inline-block"><?= $paymentStatus === 'paid' ? e(t('order.more')) : e(t('order.back_to_menu')) ?></a>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
