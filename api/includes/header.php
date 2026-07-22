<?php
/** Expects optional $active = 'home'|'menu'|'reservations'|'profile' */
$active = $active ?? '';
$user = current_user();
function nav_link(string $href, string $label, string $key, string $active): string
{
    $isActive = $active === $key;
    $cls = $isActive
        ? 'font-label-md text-label-md text-primary border-b border-primary/20 pb-1'
        : 'font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors hover:opacity-70';
    return '<a class="' . $cls . '" href="' . e($href) . '">' . e($label) . '</a>';
}

function lang_switcher_html(string $extraClass = ''): string
{
    $locale = current_locale();
    $es = e(lang_switch_url('es'));
    $en = e(lang_switch_url('en'));
    $esCls = $locale === 'es' ? 'text-primary' : 'text-on-surface-variant hover:text-primary';
    $enCls = $locale === 'en' ? 'text-primary' : 'text-on-surface-variant hover:text-primary';
    return '<div class="flex items-center gap-1 font-label-md text-label-md ' . $extraClass . '">'
        . '<a href="' . $es . '" class="' . $esCls . ' px-1">ES</a>'
        . '<span class="text-outline-variant">|</span>'
        . '<a href="' . $en . '" class="' . $enCls . ' px-1">EN</a>'
        . '</div>';
}
?>
<!--
  Header and any page-supplied $subNav share ONE fixed "chrome" surface:
  a single bg-surface/80 + backdrop-blur-md + shadow painted once. Giving
  each bar its own independent backdrop-filter (even with identical values)
  samples slightly different content behind each one and can render a
  faint seam at the boundary -- one shared box guarantees they're
  pixel-identical, because it IS the same element.
-->
<div class="fixed top-0 left-0 w-full z-50 bg-surface/80 backdrop-blur-md shadow-[0_4px_20px_rgba(93,64,55,0.08)]">
  <header>
    <div class="flex justify-between items-center px-gutter h-16 w-full max-w-container-max mx-auto">
      <div class="flex items-center gap-4">
        <button class="md:hidden text-primary flex items-center" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
          <span class="material-symbols-outlined">menu</span>
        </button>
        <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-1.5 font-wordmark font-extrabold text-[22px] leading-none tracking-tight text-primary">
          <span class="w-2 h-2 rounded-full bg-accent"></span>FRANCA
        </a>
      </div>
      <nav class="hidden md:flex items-center gap-8">
        <?= nav_link(BASE_URL . '/index.php', t('nav.home'), 'home', $active) ?>
        <?= nav_link(BASE_URL . '/menu.php', t('nav.menu'), 'menu', $active) ?>
        <?= nav_link(BASE_URL . '/reservations.php', t('nav.reservations'), 'reservations', $active) ?>
        <?= $user ? nav_link(BASE_URL . '/profile.php', t('nav.profile'), 'profile', $active) : nav_link(BASE_URL . '/login.php', t('nav.login'), 'login', $active) ?>
      </nav>
      <div class="flex items-center gap-4">
        <?= lang_switcher_html('hidden md:flex') ?>
        <a href="<?= BASE_URL ?>/cart.php" class="relative text-primary hover:opacity-80 transition-opacity">
          <span class="material-symbols-outlined">shopping_bag</span>
          <?php if (cart_count() > 0): ?>
          <span class="absolute -top-1 -right-1 bg-secondary text-on-secondary text-[10px] font-label-md rounded-full w-4 h-4 flex items-center justify-center"><?= cart_count() ?></span>
          <?php endif; ?>
        </a>
      </div>
    </div>
    <div id="mobile-menu" class="hidden md:hidden bg-surface-container-lowest border-t border-outline-variant/30 px-gutter py-md flex flex-col gap-3">
      <?= nav_link(BASE_URL . '/index.php', t('nav.home'), 'home', $active) ?>
      <?= nav_link(BASE_URL . '/menu.php', t('nav.menu'), 'menu', $active) ?>
      <?= nav_link(BASE_URL . '/reservations.php', t('nav.reservations'), 'reservations', $active) ?>
      <?php if ($user): ?>
        <?= nav_link(BASE_URL . '/profile.php', t('nav.profile'), 'profile', $active) ?>
        <a class="font-label-md text-label-md text-on-surface-variant" href="<?= BASE_URL ?>/logout.php"><?= e(t('nav.logout')) ?></a>
      <?php else: ?>
        <?= nav_link(BASE_URL . '/login.php', t('nav.login'), 'login', $active) ?>
        <?= nav_link(BASE_URL . '/register.php', t('nav.register'), 'register', $active) ?>
      <?php endif; ?>
      <?= lang_switcher_html('pt-2 border-t border-outline-variant/30 mt-1') ?>
    </div>
  </header>
  <?= $subNav ?? '' ?>
</div>
<?php if (!empty($_SESSION['flash'])): ?>
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-[60] w-[92%] max-w-md space-y-2">
  <?php foreach ($_SESSION['flash'] as $key => $msg): if (!$msg) continue; ?>
    <div class="<?= $key === 'error' ? 'bg-error-container text-on-error-container' : 'bg-secondary-container text-on-secondary-container' ?> px-md py-3 rounded-lg shadow-lg font-label-md text-sm text-center"><?= e($msg) ?></div>
  <?php endforeach; unset($_SESSION['flash']); ?>
</div>
<?php endif; ?>
<main class="<?= isset($subNav) ? 'pt-[126px]' : 'pt-24' ?> pb-32 md:pb-16 max-w-container-max mx-auto px-gutter">
