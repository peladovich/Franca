<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n.php';
require_login();

$user = current_user();

$ordersStmt = db()->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$ordersStmt->execute([$user['id']]);
$orders = $ordersStmt->fetchAll();

$resStmt = db()->prepare("SELECT * FROM reservations WHERE user_id = ? ORDER BY reservation_date DESC, reservation_time DESC");
$resStmt->execute([$user['id']]);
$reservations = $resStmt->fetchAll();

$statusColors = [
    'pending' => 'bg-secondary-container text-on-secondary-container',
    'confirmed' => 'bg-secondary-container text-on-secondary-container',
    'preparing' => 'bg-secondary-container text-on-secondary-container',
    'ready' => 'bg-secondary text-on-secondary',
    'completed' => 'bg-surface-container-high text-on-surface-variant',
    'cancelled' => 'bg-error-container text-on-error-container',
];

$pageTitle = t('profile.title') . ' | Franca';
$active = 'profile';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-lg">
  <h1 class="font-headline-md text-headline-md text-primary"><?= e(t('profile.hi', ['name' => $user['name']])) ?></h1>
  <p class="font-body-md text-on-surface-variant"><?= e($user['email']) ?></p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
  <section>
    <h2 class="font-headline-sm text-headline-sm text-primary mb-md"><?= e(t('profile.your_orders')) ?></h2>
    <?php if (!$orders): ?>
      <p class="font-body-md text-on-surface-variant"><?= e(t('profile.no_orders')) ?> <a class="text-secondary underline" href="<?= BASE_URL ?>/menu.php"><?= e(t('profile.browse_menu_link')) ?></a>.</p>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($orders as $o): ?>
        <a href="<?= BASE_URL ?>/order-confirmation.php?id=<?= (int)$o['id'] ?>" class="block bg-surface-container-lowest rounded-xl p-md flex justify-between items-center hover:shadow-md transition-shadow">
          <div>
            <p class="font-label-md text-primary"><?= e(t('profile.order')) ?> #<?= (int)$o['id'] ?> · <?= e(service_mode_label($o['service_mode'])) ?></p>
            <p class="font-caption text-on-surface-variant"><?= e(date('M j, Y g:i A', strtotime($o['created_at']))) ?></p>
          </div>
          <div class="text-right">
            <p class="font-label-md text-accent-dark"><?= money($o['total']) ?></p>
            <?php if ($o['payment_status'] !== 'paid'): ?>
              <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-label-md uppercase bg-error-container text-on-error-container"><?= $o['payment_status'] === 'pending' ? e(t('profile.unpaid')) : e(status_label($o['payment_status'])) ?></span>
            <?php else: ?>
              <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-label-md uppercase <?= $statusColors[$o['status']] ?? '' ?>"><?= e(status_label($o['status'])) ?></span>
            <?php endif; ?>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section>
    <h2 class="font-headline-sm text-headline-sm text-primary mb-md"><?= e(t('profile.your_reservations')) ?></h2>
    <?php if (!$reservations): ?>
      <p class="font-body-md text-on-surface-variant"><?= e(t('profile.no_reservations')) ?> <a class="text-secondary underline" href="<?= BASE_URL ?>/reservations.php"><?= e(t('profile.book_table_link')) ?></a>.</p>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($reservations as $r): ?>
        <div class="bg-surface-container-lowest rounded-xl p-md flex justify-between items-center">
          <div>
            <p class="font-label-md text-primary"><?= e(date('M j, Y', strtotime($r['reservation_date']))) ?> <?= e(t('profile.at')) ?> <?= e(date('g:i A', strtotime($r['reservation_time']))) ?></p>
            <p class="font-caption text-on-surface-variant"><?= (int)$r['party_size'] ?> <?= e(t('profile.guests')) ?></p>
          </div>
          <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-label-md uppercase <?= $statusColors[$r['status']] ?? '' ?>"><?= e(status_label($r['status'])) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>

<div class="mt-xl">
  <a href="<?= BASE_URL ?>/logout.php" class="text-error font-label-md underline"><?= e(t('profile.log_out')) ?></a>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
