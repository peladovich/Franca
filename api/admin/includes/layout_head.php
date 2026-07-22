<?php
/** Expects $pageTitle and $active to be set. Requires admin. */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/i18n.php';
require_admin();

$active = $active ?? '';
$admin = current_user();

function admin_link(string $href, string $icon, string $label, string $key, string $active): string
{
    $isActive = $active === $key;
    $cls = $isActive
        ? 'flex items-center gap-3 px-4 py-3 rounded-lg bg-accent/15 text-accent-dark font-label-md'
        : 'flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-high font-label-md transition-colors';
    return '<a href="' . e($href) . '" class="' . $cls . '"><span class="material-symbols-outlined text-[20px]">' . $icon . '</span>' . e($label) . '</a>';
}

require __DIR__ . '/../../includes/head.php';
?>
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="hidden md:flex md:w-64 flex-shrink-0 bg-surface-container-lowest border-r border-outline-variant/30 flex-col fixed top-0 left-0 h-screen z-40">
    <div class="px-6 py-6 border-b border-outline-variant/30">
      <a href="<?= BASE_URL ?>/admin/index.php" class="flex items-center gap-1.5 font-wordmark font-extrabold text-[20px] leading-none tracking-tight text-primary">
        <img src="<?= BASE_URL ?>/assets/img/brand/logo-mark.png" alt="" class="w-5 h-5 object-contain">FRANCA
      </a>
      <p class="font-eyebrow text-[10px] text-on-surface-variant uppercase tracking-[0.15em] mt-2"><?= e(t('admin.panel_label')) ?></p>
    </div>
    <nav class="flex-1 px-3 py-4 space-y-1">
      <?= admin_link(BASE_URL . '/admin/index.php', 'dashboard', t('admin.nav_dashboard'), 'dashboard', $active) ?>
      <?= admin_link(BASE_URL . '/admin/menu-items.php', 'restaurant_menu', t('admin.nav_menu_items'), 'menu-items', $active) ?>
      <?= admin_link(BASE_URL . '/admin/categories.php', 'category', t('admin.nav_categories'), 'categories', $active) ?>
      <?= admin_link(BASE_URL . '/admin/orders.php', 'shopping_bag', t('admin.nav_orders'), 'orders', $active) ?>
      <?= admin_link(BASE_URL . '/admin/reservations.php', 'event_available', t('admin.nav_reservations'), 'reservations', $active) ?>
      <?= admin_link(BASE_URL . '/admin/users.php', 'group', t('admin.nav_users'), 'users', $active) ?>
      <?= admin_link(BASE_URL . '/admin/settings.php', 'settings', t('admin.nav_settings'), 'settings', $active) ?>
    </nav>
    <div class="px-3 py-4 border-t border-outline-variant/30">
      <div class="flex items-center justify-between px-4 mb-3">
        <p class="font-caption text-on-surface-variant"><?= e($admin['name']) ?></p>
        <?= lang_switcher_html() ?>
      </div>
      <a href="<?= BASE_URL ?>/admin/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-error hover:bg-error-container/30 font-label-md"><span class="material-symbols-outlined text-[20px]">logout</span><?= e(t('admin.log_out')) ?></a>
    </div>
  </aside>

  <!-- Mobile top bar -->
  <header class="md:hidden fixed top-0 left-0 w-full z-50 bg-surface/95 backdrop-blur-md border-b border-outline-variant/30 flex items-center justify-between px-gutter h-16">
    <a href="<?= BASE_URL ?>/admin/index.php" class="flex items-center gap-1.5 font-wordmark font-extrabold text-[18px] leading-none tracking-tight text-primary">
      <img src="<?= BASE_URL ?>/assets/img/brand/logo-mark.png" alt="" class="w-5 h-5 object-contain">FRANCA
    </a>
    <div class="flex items-center gap-3">
      <?= lang_switcher_html() ?>
      <button onclick="document.getElementById('admin-mobile-nav').classList.toggle('hidden')" class="text-primary flex items-center"><span class="material-symbols-outlined">menu</span></button>
    </div>
  </header>
  <nav id="admin-mobile-nav" class="hidden md:hidden fixed top-16 left-0 w-full z-50 bg-surface-container-lowest border-b border-outline-variant/30 px-gutter py-md flex flex-col gap-1">
    <?= admin_link(BASE_URL . '/admin/index.php', 'dashboard', t('admin.nav_dashboard'), 'dashboard', $active) ?>
    <?= admin_link(BASE_URL . '/admin/menu-items.php', 'restaurant_menu', t('admin.nav_menu_items'), 'menu-items', $active) ?>
    <?= admin_link(BASE_URL . '/admin/categories.php', 'category', t('admin.nav_categories'), 'categories', $active) ?>
    <?= admin_link(BASE_URL . '/admin/orders.php', 'shopping_bag', t('admin.nav_orders'), 'orders', $active) ?>
    <?= admin_link(BASE_URL . '/admin/reservations.php', 'event_available', t('admin.nav_reservations'), 'reservations', $active) ?>
    <?= admin_link(BASE_URL . '/admin/users.php', 'group', t('admin.nav_users'), 'users', $active) ?>
    <?= admin_link(BASE_URL . '/admin/settings.php', 'settings', t('admin.nav_settings'), 'settings', $active) ?>
    <a href="<?= BASE_URL ?>/admin/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-error font-label-md"><span class="material-symbols-outlined text-[20px]">logout</span><?= e(t('admin.log_out')) ?></a>
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
