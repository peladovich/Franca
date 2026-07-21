<?php
/** Expects $pageTitle and $active to be set. Requires admin. */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_admin();

$active = $active ?? '';
$admin = current_user();

function admin_link(string $href, string $icon, string $label, string $key, string $active): string
{
    $isActive = $active === $key;
    $cls = $isActive
        ? 'flex items-center gap-3 px-4 py-3 rounded-lg bg-secondary-container text-on-secondary-container font-label-md'
        : 'flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-high font-label-md transition-colors';
    return '<a href="' . e($href) . '" class="' . $cls . '"><span class="material-symbols-outlined text-[20px]">' . $icon . '</span>' . e($label) . '</a>';
}

require __DIR__ . '/../../includes/head.php';
?>
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="hidden md:flex md:w-64 flex-shrink-0 bg-surface-container-lowest border-r border-outline-variant/30 flex-col fixed top-0 left-0 h-screen z-40">
    <div class="px-6 py-6 border-b border-outline-variant/30">
      <span class="font-display-lg-mobile text-display-lg-mobile text-primary">FRANCA</span>
      <p class="font-caption text-on-surface-variant uppercase tracking-widest">Admin Panel</p>
    </div>
    <nav class="flex-1 px-3 py-4 space-y-1">
      <?= admin_link(BASE_URL . '/admin/index.php', 'dashboard', 'Dashboard', 'dashboard', $active) ?>
      <?= admin_link(BASE_URL . '/admin/menu-items.php', 'restaurant_menu', 'Menu Items', 'menu-items', $active) ?>
      <?= admin_link(BASE_URL . '/admin/categories.php', 'category', 'Categories', 'categories', $active) ?>
      <?= admin_link(BASE_URL . '/admin/orders.php', 'shopping_bag', 'Orders', 'orders', $active) ?>
      <?= admin_link(BASE_URL . '/admin/reservations.php', 'event_available', 'Reservations', 'reservations', $active) ?>
      <?= admin_link(BASE_URL . '/admin/users.php', 'group', 'Users', 'users', $active) ?>
      <?= admin_link(BASE_URL . '/admin/settings.php', 'settings', 'Settings', 'settings', $active) ?>
    </nav>
    <div class="px-3 py-4 border-t border-outline-variant/30">
      <p class="font-caption text-on-surface-variant px-4 mb-2"><?= e($admin['name']) ?></p>
      <a href="<?= BASE_URL ?>/admin/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-error hover:bg-error-container/30 font-label-md"><span class="material-symbols-outlined text-[20px]">logout</span>Log out</a>
    </div>
  </aside>

  <!-- Mobile top bar -->
  <header class="md:hidden fixed top-0 left-0 w-full z-50 bg-surface/95 backdrop-blur-md border-b border-outline-variant/30 flex items-center justify-between px-gutter h-16">
    <span class="font-display-lg-mobile text-display-lg-mobile text-primary">FRANCA Admin</span>
    <button onclick="document.getElementById('admin-mobile-nav').classList.toggle('hidden')" class="text-primary"><span class="material-symbols-outlined">menu</span></button>
  </header>
  <nav id="admin-mobile-nav" class="hidden md:hidden fixed top-16 left-0 w-full z-50 bg-surface-container-lowest border-b border-outline-variant/30 px-gutter py-md flex flex-col gap-1">
    <?= admin_link(BASE_URL . '/admin/index.php', 'dashboard', 'Dashboard', 'dashboard', $active) ?>
    <?= admin_link(BASE_URL . '/admin/menu-items.php', 'restaurant_menu', 'Menu Items', 'menu-items', $active) ?>
    <?= admin_link(BASE_URL . '/admin/categories.php', 'category', 'Categories', 'categories', $active) ?>
    <?= admin_link(BASE_URL . '/admin/orders.php', 'shopping_bag', 'Orders', 'orders', $active) ?>
    <?= admin_link(BASE_URL . '/admin/reservations.php', 'event_available', 'Reservations', 'reservations', $active) ?>
    <?= admin_link(BASE_URL . '/admin/users.php', 'group', 'Users', 'users', $active) ?>
    <?= admin_link(BASE_URL . '/admin/settings.php', 'settings', 'Settings', 'settings', $active) ?>
    <a href="<?= BASE_URL ?>/admin/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-error font-label-md"><span class="material-symbols-outlined text-[20px]">logout</span>Log out</a>
  </nav>

  <!-- Main content -->
  <main class="flex-1 md:ml-64 pt-20 md:pt-8 pb-16 px-gutter max-w-6xl w-full">
    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="mb-md space-y-2">
        <?php foreach ($_SESSION['flash'] as $key => $msg): if (!$msg) continue; ?>
          <div class="<?= $key === 'error' ? 'bg-error-container text-on-error-container' : 'bg-secondary-container text-on-secondary-container' ?> px-md py-3 rounded-lg font-label-md text-sm"><?= e($msg) ?></div>
        <?php endforeach; unset($_SESSION['flash']); ?>
      </div>
    <?php endif; ?>
