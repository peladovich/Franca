<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n.php';

$categories = db()->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$itemsStmt = db()->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1 ORDER BY sort_order");

// Rendered as a true sibling of <main> (via header.php's $subNav hook) so it can
// bleed to the real viewport edges the same way the site header does, with no
// vw/calc breakout hacks that risk phantom horizontal scroll.
ob_start(); ?>
<nav id="category-nav" class="w-full">
  <div class="max-w-container-max 2xl:max-w-[1600px] mx-auto overflow-x-auto hide-scrollbar">
    <div class="flex gap-2 items-center w-max px-gutter py-3">
      <?php foreach ($categories as $i => $cat): ?>
        <a href="#cat-<?= (int)$cat['id'] ?>" data-cat-pill data-target="cat-<?= (int)$cat['id'] ?>" class="cat-pill <?= $i === 0 ? 'is-active bg-primary text-on-primary border-primary' : 'bg-surface-container-low text-on-surface-variant border-outline-variant/40' ?> font-label-md text-label-md whitespace-nowrap px-4 py-2 rounded-full border"><?= e(mi_field($cat, 'name')) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</nav>
<?php $subNav = ob_get_clean();

$pageTitle = 'Franca | ' . t('nav.menu');
$active = 'menu';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<header class="reveal mb-2xl text-center md:text-left">
  <span class="font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em] block mb-3"><?= e(t('menu.eyebrow')) ?></span>
  <h1 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-primary max-w-2xl"><?= e(t('menu.title')) ?></h1>
</header>

<?php foreach ($categories as $cat):
  $itemsStmt->execute([$cat['id']]);
  $items = $itemsStmt->fetchAll();
  if (!$items) continue;
?>
<section id="cat-<?= (int)$cat['id'] ?>" class="scroll-mt-32 mb-2xl">
  <div class="reveal border-b border-outline-variant/30 pb-3 mb-lg">
    <h2 class="font-headline-md text-headline-md text-primary"><?= e(mi_field($cat, 'name')) ?></h2>
  </div>
  <div class="grid grid-cols-2 lg:grid-cols-3 gap-x-md gap-y-lg">
    <?php foreach ($items as $item): ?>
    <a href="<?= BASE_URL ?>/dish.php?id=<?= (int)$item['id'] ?>" class="group block">
      <div class="aspect-square overflow-hidden bg-surface-container-low mb-3">
        <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="<?= item_photo_url($item) ?>" alt="<?= e(mi_field($item, 'name')) ?>">
      </div>
      <div class="flex justify-between items-start gap-2">
        <h3 class="font-label-md text-label-md text-primary uppercase tracking-wide"><?= e(mi_field($item, 'name')) ?></h3>
        <span class="font-body-md text-body-md text-accent-dark whitespace-nowrap"><?= money($item['price']) ?></span>
      </div>
      <?php if ($item['badge']): ?>
        <span class="inline-block mt-1 px-2 py-0.5 bg-secondary text-on-secondary text-[10px] font-label-md rounded-full uppercase tracking-wider"><?= e(mi_field($item, 'badge')) ?></span>
      <?php endif; ?>
      <p class="font-body-md text-body-md text-on-surface-variant mt-1 line-clamp-2"><?= e(mi_field($item, 'description')) ?></p>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endforeach; ?>

<?php $menuNotes = current_locale() === 'en' ? get_setting('menu_notes_en', get_setting('menu_notes')) : get_setting('menu_notes'); if ($menuNotes): ?>
<div class="border-t border-outline-variant/30 pt-md">
  <p class="font-caption text-on-surface-variant"><?= e($menuNotes) ?></p>
</div>
<?php endif; ?>

<script>
(function () {
  var ACTIVE_CLASSES = ['is-active', 'bg-primary', 'text-on-primary', 'border-primary'];
  var INACTIVE_CLASSES = ['bg-surface-container-low', 'text-on-surface-variant', 'border-outline-variant/40'];

  var pills = Array.prototype.slice.call(document.querySelectorAll('[data-cat-pill]'));
  var pillsByTarget = {};
  pills.forEach(function (p) { pillsByTarget[p.dataset.target] = p; });

  var pillScroller = document.querySelector('#category-nav .overflow-x-auto');

  function setActivePill(targetId) {
    pills.forEach(function (p) {
      var isMatch = p.dataset.target === targetId;
      ACTIVE_CLASSES.forEach(function (c) { p.classList.toggle(c, isMatch); });
      INACTIVE_CLASSES.forEach(function (c) { p.classList.toggle(c, !isMatch); });
    });
    var activePill = pillsByTarget[targetId];
    if (activePill && pillScroller) {
      var pillLeft = activePill.offsetLeft;
      var pillRight = pillLeft + activePill.offsetWidth;
      var viewLeft = pillScroller.scrollLeft;
      var viewRight = viewLeft + pillScroller.clientWidth;
      if (pillLeft < viewLeft || pillRight > viewRight) {
        activePill.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
      }
    }
  }

  // Click gives immediate feedback instead of waiting for the scroll to
  // settle and the observer below to catch up.
  pills.forEach(function (p) {
    p.addEventListener('click', function () { setActivePill(p.dataset.target); });
  });

  // Scroll-spy: a section is "active" once it crosses a thin detection
  // band just below the fixed header + category bar, so the highlighted
  // pill always matches whatever section is actually on screen.
  var sections = Array.prototype.slice.call(document.querySelectorAll('section[id^="cat-"]'));
  if (sections.length && 'IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      var visible = entries.filter(function (e) { return e.isIntersecting; });
      if (!visible.length) return;
      // If multiple sections intersect the band at once, prefer the one
      // closest to the top of the viewport.
      visible.sort(function (a, b) { return a.boundingClientRect.top - b.boundingClientRect.top; });
      setActivePill(visible[0].target.id);
    }, { rootMargin: '-135px 0px -60% 0px', threshold: 0 });
    sections.forEach(function (s) { observer.observe(s); });
  }
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
