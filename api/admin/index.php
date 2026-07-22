<?php
$pageTitle = 'Dashboard | Franca Admin';
$active = 'dashboard';
require __DIR__ . '/includes/layout_head.php';

$db = db();
$stats = [
    'menu_items' => $db->query("SELECT COUNT(*) FROM menu_items")->fetchColumn(),
    'orders_today' => $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'paid'")->fetchColumn(),
    'revenue_today' => $db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'paid'")->fetchColumn(),
    'reservations_upcoming' => $db->query("SELECT COUNT(*) FROM reservations WHERE reservation_date >= CURDATE() AND status != 'cancelled'")->fetchColumn(),
    'users' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
];

$recentOrders = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
$upcomingRes = $db->query("SELECT * FROM reservations WHERE status != 'cancelled' ORDER BY reservation_date ASC, reservation_time ASC LIMIT 5")->fetchAll();
?>

<h1 class="font-headline-md text-headline-md text-primary mb-lg">Dashboard</h1>

<div class="grid grid-cols-2 lg:grid-cols-5 gap-md mb-xl">
  <div class="bg-surface-container-lowest rounded-xl p-md editorial-shadow">
    <p class="font-caption text-on-surface-variant uppercase tracking-wide mb-1">Menu Items</p>
    <p class="font-headline-md text-headline-md text-primary"><?= (int)$stats['menu_items'] ?></p>
  </div>
  <div class="bg-surface-container-lowest rounded-xl p-md editorial-shadow">
    <p class="font-caption text-on-surface-variant uppercase tracking-wide mb-1">Orders Today</p>
    <p class="font-headline-md text-headline-md text-primary"><?= (int)$stats['orders_today'] ?></p>
  </div>
  <div class="bg-surface-container-lowest rounded-xl p-md editorial-shadow">
    <p class="font-caption text-on-surface-variant uppercase tracking-wide mb-1">Revenue Today</p>
    <p class="font-headline-md text-headline-md text-primary"><?= money((float)$stats['revenue_today']) ?></p>
  </div>
  <div class="bg-surface-container-lowest rounded-xl p-md editorial-shadow">
    <p class="font-caption text-on-surface-variant uppercase tracking-wide mb-1">Upcoming Reservations</p>
    <p class="font-headline-md text-headline-md text-primary"><?= (int)$stats['reservations_upcoming'] ?></p>
  </div>
  <div class="bg-surface-container-lowest rounded-xl p-md editorial-shadow">
    <p class="font-caption text-on-surface-variant uppercase tracking-wide mb-1">Customers</p>
    <p class="font-headline-md text-headline-md text-primary"><?= (int)$stats['users'] ?></p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">
  <section>
    <div class="flex justify-between items-center mb-md">
      <h2 class="font-headline-sm text-headline-sm text-primary">Recent Orders</h2>
      <a href="<?= BASE_URL ?>/admin/orders.php" class="font-label-md text-secondary text-sm">View all</a>
    </div>
    <?php if (!$recentOrders): ?>
      <p class="font-body-md text-on-surface-variant">No orders yet.</p>
    <?php else: ?>
      <div class="space-y-2">
        <?php foreach ($recentOrders as $o): ?>
        <div class="bg-surface-container-lowest rounded-lg p-3 flex justify-between items-center">
          <span class="font-body-md">#<?= (int)$o['id'] ?> · <?= e($o['guest_name'] ?: 'Registered user') ?></span>
          <span class="flex items-center gap-2">
            <?php if ($o['payment_status'] !== 'paid'): ?><span class="text-[10px] font-label-md uppercase text-error"><?= e($o['payment_status']) ?></span><?php endif; ?>
            <span class="font-label-md text-secondary"><?= money($o['total']) ?></span>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section>
    <div class="flex justify-between items-center mb-md">
      <h2 class="font-headline-sm text-headline-sm text-primary">Upcoming Reservations</h2>
      <a href="<?= BASE_URL ?>/admin/reservations.php" class="font-label-md text-secondary text-sm">View all</a>
    </div>
    <?php if (!$upcomingRes): ?>
      <p class="font-body-md text-on-surface-variant">No upcoming reservations.</p>
    <?php else: ?>
      <div class="space-y-2">
        <?php foreach ($upcomingRes as $r): ?>
        <div class="bg-surface-container-lowest rounded-lg p-3 flex justify-between items-center">
          <span class="font-body-md"><?= e($r['name']) ?> · <?= (int)$r['party_size'] ?>p</span>
          <span class="font-caption text-on-surface-variant"><?= e(date('M j, g:i A', strtotime($r['reservation_date'] . ' ' . $r['reservation_time']))) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
