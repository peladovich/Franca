<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n.php';

$categories = db()->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$itemsStmt = db()->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1 ORDER BY sort_order");

$pageTitle = 'Franca | ' . t('nav.menu');
$active = 'menu';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<header class="mb-lg text-center md:text-left">
  <span class="font-label-md text-secondary uppercase tracking-widest block mb-2"><?= e(t('menu.eyebrow')) ?></span>
  <h1 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-primary max-w-2xl"><?= e(t('menu.title')) ?></h1>
</header>

<!-- Category pills -->
<nav id="category-nav" class="sticky top-24 z-30 bg-surface shadow-[0_4px_16px_rgba(93,64,55,0.10)] border-b border-outline-variant/20 -mx-gutter px-gutter py-3 mb-lg overflow-x-auto hide-scrollbar">
  <div class="flex gap-2 items-center w-max">
    <?php foreach ($categories as $i => $cat): ?>
      <a href="#cat-<?= (int)$cat['id'] ?>" data-cat-pill data-target="cat-<?= (int)$cat['id'] ?>" class="cat-pill <?= $i === 0 ? 'is-active bg-primary text-on-primary border-primary' : 'bg-surface-container-low text-on-surface-variant border-outline-variant/40' ?> font-label-md text-label-md whitespace-nowrap px-4 py-2 rounded-full border"><?= e(mi_field($cat, 'name')) ?></a>
    <?php endforeach; ?>
  </div>
</nav>

<?php foreach ($categories as $cat):
  $itemsStmt->execute([$cat['id']]);
  $items = $itemsStmt->fetchAll();
  if (!$items) continue;
?>
<section id="cat-<?= (int)$cat['id'] ?>" class="scroll-mt-32 mb-xl">
  <div class="border-b border-outline-variant/30 pb-2 mb-lg">
    <h2 class="font-headline-md text-headline-md text-primary"><?= e(mi_field($cat, 'name')) ?></h2>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
    <?php foreach ($items as $item): ?>
    <a href="<?= BASE_URL ?>/dish.php?id=<?= (int)$item['id'] ?>" class="menu-item-card bg-surface-container-lowest rounded-xl overflow-hidden flex gap-md p-md items-center group">
      <div class="w-24 h-24 flex-shrink-0 rounded-lg overflow-hidden">
        <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" src="<?= img_url($item['image']) ?>" alt="<?= e(mi_field($item, 'name')) ?>">
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex justify-between items-start gap-2">
          <h3 class="font-headline-sm text-headline-sm text-primary"><?= e(mi_field($item, 'name')) ?></h3>
          <span class="font-body-lg text-body-lg text-secondary whitespace-nowrap"><?= money($item['price']) ?></span>
        </div>
        <?php if ($item['badge']): ?>
          <span class="inline-block mt-1 px-2 py-0.5 bg-secondary text-on-secondary text-[10px] font-label-md rounded-full uppercase tracking-wider"><?= e(mi_field($item, 'badge')) ?></span>
        <?php endif; ?>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1 line-clamp-2"><?= e(mi_field($item, 'description')) ?></p>
      </div>
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

<?php require __DIR__ . '/includes/footer.php'; ?>
