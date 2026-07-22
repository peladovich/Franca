<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n.php';

$featured = db()->query("SELECT * FROM menu_items WHERE is_featured = 1 AND is_available = 1 ORDER BY sort_order LIMIT 6")->fetchAll();

$pageTitle = 'Franca - ' . t('home.hero_title');
$active = 'home';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="mb-xl">
  <div class="relative overflow-hidden rounded-photo h-[420px] md:h-[500px] group">
    <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105"
         style="background-image: url('<?= img_url('real/storefront.jpg') ?>')"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-primary/70 to-transparent"></div>
    <div class="absolute bottom-0 left-0 p-md md:p-xl w-full">
      <h1 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-white mb-2"><?= e(t('home.hero_title')) ?></h1>
      <p class="font-body-lg text-white/90 mb-md max-w-md"><?= e(t('home.hero_subtitle')) ?></p>
      <div class="flex gap-sm">
        <a href="<?= BASE_URL ?>/menu.php" class="btn-lift bg-accent text-on-accent px-md py-3 rounded-full font-label-md"><?= e(t('home.view_menu')) ?></a>
        <a href="<?= BASE_URL ?>/reservations.php" class="bg-white/20 backdrop-blur-md text-white border border-white/30 px-md py-3 rounded-full font-label-md hover:bg-white/30 transition-all"><?= e(t('home.book_table')) ?></a>
      </div>
    </div>
  </div>
</section>

<!-- Popular Today -->
<section class="mb-xl">
  <div class="flex justify-between items-end mb-md">
    <div>
      <h2 class="font-headline-md text-headline-md text-primary"><?= e(t('home.popular_today')) ?></h2>
      <p class="font-body-md text-on-surface-variant"><?= e(t('home.popular_subtitle')) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/menu.php" class="text-secondary font-label-md flex items-center"><?= e(t('home.see_all')) ?> <span class="material-symbols-outlined ml-1">chevron_right</span></a>
  </div>
  <div class="flex overflow-x-auto hide-scrollbar gap-md pb-4">
    <?php foreach ($featured as $item): ?>
    <div class="flex-shrink-0 w-64 bg-surface-container-lowest rounded-photo shadow-sm overflow-hidden menu-item-card flex flex-col">
      <a href="<?= BASE_URL ?>/dish.php?id=<?= (int)$item['id'] ?>">
        <div class="h-48 bg-cover bg-center" style="background-image: url('<?= img_url($item['image']) ?>')"></div>
      </a>
      <div class="p-md flex flex-col flex-1">
        <div class="flex justify-between items-start gap-2 mb-1">
          <h3 class="font-headline-sm text-headline-sm line-clamp-2"><?= e(mi_field($item, 'name')) ?></h3>
          <span class="font-label-md text-accent-dark whitespace-nowrap"><?= money($item['price']) ?></span>
        </div>
        <p class="font-caption text-on-surface-variant line-clamp-2 mb-md flex-1"><?= e(mi_field($item, 'description')) ?></p>
        <form method="post" action="<?= BASE_URL ?>/cart.php">
          <?= csrf_field() ?>
          <input type="hidden" name="menu_item_id" value="<?= (int)$item['id'] ?>">
          <input type="hidden" name="action" value="add">
          <button class="btn-lift w-full border border-secondary text-secondary py-2 rounded-lg font-label-md hover:bg-secondary-container transition-colors" type="submit"><?= e(t('home.add_to_bag')) ?></button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Our Vibe -->
<section class="max-w-container-max mx-auto mb-xl">
  <h2 class="font-headline-md text-headline-md text-primary mb-lg"><?= e(t('home.our_vibe')) ?></h2>
  <div class="space-y-lg">
    <div class="grid md:grid-cols-2 gap-md items-center">
      <div class="rounded-photo overflow-hidden aspect-[4/3] shadow-lg">
        <img class="w-full h-full object-cover" src="<?= img_url('real/interior-morning.jpg') ?>" alt="Morning ritual at Franca">
      </div>
      <div class="space-y-2">
        <span class="font-label-md text-accent-dark uppercase tracking-widest"><?= e(t('home.atmosphere_label')) ?></span>
        <h3 class="font-headline-sm text-headline-sm"><?= e(t('home.morning_ritual_title')) ?></h3>
        <p class="font-body-md text-on-surface-variant"><?= e(t('home.morning_ritual_text')) ?></p>
      </div>
    </div>
    <div class="grid md:grid-cols-2 gap-md items-center">
      <div class="md:order-2 rounded-photo overflow-hidden aspect-[4/3] shadow-lg">
        <img class="w-full h-full object-cover" src="<?= img_url('real/team-kitchen.jpg') ?>" alt="Mindful craft at Franca">
      </div>
      <div class="md:order-1 space-y-2">
        <span class="font-label-md text-accent-dark uppercase tracking-widest"><?= e(t('home.process_label')) ?></span>
        <h3 class="font-headline-sm text-headline-sm"><?= e(t('home.mindful_craft_title')) ?></h3>
        <p class="font-body-md text-on-surface-variant"><?= e(t('home.mindful_craft_text')) ?></p>
      </div>
    </div>
  </div>
</section>

<!-- Newsletter -->
<section class="px-gutter py-xl bg-secondary-container rounded-[40px] mb-xl">
  <div class="max-w-lg mx-auto text-center space-y-md">
    <h2 class="font-headline-md text-headline-md text-on-secondary-container"><?= e(t('home.newsletter_title')) ?></h2>
    <p class="font-body-md text-on-secondary-container"><?= e(t('home.newsletter_text')) ?></p>
    <form method="post" action="<?= BASE_URL ?>/newsletter.php" class="flex flex-col sm:flex-row gap-sm">
      <?= csrf_field() ?>
      <input class="flex-1 bg-white border-none rounded-lg px-md py-3 focus:ring-2 focus:ring-secondary transition-all" placeholder="<?= e(t('home.email_placeholder')) ?>" type="email" name="email" required>
      <button class="btn-lift bg-primary text-on-primary px-lg py-3 rounded-lg font-label-md" type="submit"><?= e(t('home.join')) ?></button>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
