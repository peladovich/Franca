<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT mi.*, c.name AS category_name, c.name_en AS category_name_en FROM menu_items mi JOIN categories c ON c.id = mi.category_id WHERE mi.id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    $pageTitle = t('dish.not_found') . ' | Franca';
    require __DIR__ . '/includes/head.php';
    require __DIR__ . '/includes/header.php';
    echo '<div class="text-center py-xl"><p class="font-headline-sm text-headline-sm">' . e(t('dish.not_found')) . '</p><a class="text-secondary underline" href="' . BASE_URL . '/menu.php">' . e(t('dish.back_to_menu')) . '</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$ingredients = array_filter(array_map('trim', explode(',', mi_field($item, 'ingredients'))));

$pageTitle = 'Franca | ' . mi_field($item, 'name');
$active = 'menu';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<div class="reveal-group flex flex-col md:flex-row md:gap-lg">
  <div class="w-full md:w-1/2 lg:w-7/12 relative">
    <div class="aspect-[4/5] md:aspect-square overflow-hidden rounded-photo editorial-shadow bg-surface-container-highest">
      <img class="w-full h-full object-cover" src="<?= item_photo_url($item) ?>" alt="<?= e(mi_field($item, 'name')) ?>">
    </div>
    <div class="absolute bottom-6 right-6 bg-surface-container-lowest py-3 px-6 rounded-lg shadow-lg flex flex-col items-end">
      <span class="font-eyebrow text-[10px] text-on-surface-variant opacity-70 uppercase tracking-[0.15em]"><?= e(t('dish.price_label')) ?></span>
      <span class="font-headline-md text-headline-md text-accent-dark"><?= money($item['price']) ?></span>
    </div>
  </div>

  <div class="w-full md:w-1/2 lg:w-5/12 mt-md md:mt-0 flex flex-col justify-center">
    <span class="font-eyebrow text-[11px] text-accent-dark tracking-[0.2em] uppercase"><?= e(mi_field($item, 'category_name')) ?></span>
    <h1 class="font-display-lg text-display-lg text-primary mt-2 mb-md"><?= e(mi_field($item, 'name')) ?></h1>
    <p class="font-body-lg text-on-surface-variant mb-lg leading-relaxed"><?= e(mi_field($item, 'description')) ?></p>

    <?php if ($ingredients): ?>
    <div class="mb-lg">
      <h3 class="font-label-md text-primary mb-2"><?= e(t('dish.ingredients_label')) ?></h3>
      <div class="flex flex-wrap gap-2">
        <?php foreach ($ingredients as $ing): ?>
          <span class="px-4 py-1.5 rounded-full border border-outline-variant font-label-md text-sm text-on-surface-variant"><?= e($ing) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/cart.php" class="flex gap-4">
      <?= csrf_field() ?>
      <input type="hidden" name="menu_item_id" value="<?= (int)$item['id'] ?>">
      <input type="hidden" name="action" value="add">
      <button class="btn-lift flex-1 bg-accent text-on-accent py-4 rounded-full font-label-md uppercase tracking-widest" type="submit"><?= e(t('dish.add_to_order')) ?></button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
