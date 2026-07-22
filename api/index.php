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

<!-- Hero: full-viewport, Flora-style -->
<section class="mb-2xl -mx-gutter md:mx-0">
  <div class="relative overflow-hidden rounded-none md:rounded-photo min-h-[560px] h-[calc(100dvh-96px)] max-h-[860px]">
    <div class="absolute inset-0 bg-cover bg-center scale-105 animate-[hero-settle_1.6s_cubic-bezier(0.16,1,0.3,1)_forwards]"
         style="background-image: url('<?= img_url('real/interior-morning.jpg') ?>')"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-primary/80 via-primary/10 to-transparent"></div>
    <div class="absolute bottom-0 left-0 p-md md:p-2xl w-full">
      <span class="reveal font-eyebrow text-[12px] text-white/80 uppercase tracking-[0.2em] block mb-3"><?= e(t('menu.eyebrow')) ?></span>
      <h1 class="reveal font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-white mb-3 max-w-2xl"><?= e(t('home.hero_title')) ?></h1>
      <p class="reveal font-body-lg text-white/90 mb-lg max-w-md"><?= e(t('home.hero_subtitle')) ?></p>
      <div class="reveal flex gap-sm">
        <a href="<?= BASE_URL ?>/menu.php" class="btn-lift bg-accent text-on-accent px-md py-3 rounded-full font-label-md"><?= e(t('home.view_menu')) ?></a>
        <a href="<?= BASE_URL ?>/reservations.php" class="bg-white/20 backdrop-blur-md text-white border border-white/30 px-md py-3 rounded-full font-label-md hover:bg-white/30 transition-all"><?= e(t('home.book_table')) ?></a>
      </div>
    </div>
    <div class="scroll-cue absolute bottom-6 right-6 hidden md:flex text-white/70">
      <span class="material-symbols-outlined">arrow_downward</span>
    </div>
  </div>
</section>

<!-- Popular Today: minimal product grid, Culto/Balenciaga-style -->
<section class="mb-2xl reveal">
  <div class="flex justify-between items-end mb-lg">
    <div>
      <span class="font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em] block mb-2"><?= e(t('home.popular_today')) ?></span>
      <p class="font-body-md text-on-surface-variant"><?= e(t('home.popular_subtitle')) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/menu.php" class="link-underline text-secondary font-label-md flex items-center"><?= e(t('home.see_all')) ?></a>
  </div>
  <div class="grid grid-cols-2 md:grid-cols-3 gap-x-md gap-y-lg">
    <?php foreach ($featured as $item): ?>
    <div class="group">
      <a href="<?= BASE_URL ?>/dish.php?id=<?= (int)$item['id'] ?>" class="block relative aspect-square overflow-hidden bg-surface-container-low mb-3">
        <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="<?= item_photo_url($item) ?>" alt="<?= e(mi_field($item, 'name')) ?>">
        <form method="post" action="<?= BASE_URL ?>/cart.php" class="absolute bottom-2 right-2" onclick="event.stopPropagation()">
          <?= csrf_field() ?>
          <input type="hidden" name="menu_item_id" value="<?= (int)$item['id'] ?>">
          <input type="hidden" name="action" value="add">
          <button class="btn-lift w-9 h-9 rounded-full bg-white/90 backdrop-blur-sm text-primary flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-opacity" type="submit" title="<?= e(t('home.add_to_bag')) ?>">
            <span class="material-symbols-outlined text-[20px]">add</span>
          </button>
        </form>
      </a>
      <div class="flex justify-between items-start gap-2">
        <h3 class="font-label-md text-label-md text-primary uppercase tracking-wide"><?= e(mi_field($item, 'name')) ?></h3>
        <span class="font-body-md text-body-md text-accent-dark whitespace-nowrap"><?= money($item['price']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Our Vibe -->
<section class="max-w-container-max mx-auto mb-2xl">
  <span class="reveal font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em] block mb-3"><?= e(t('home.our_vibe')) ?></span>
  <div class="space-y-2xl">
    <div class="reveal-group grid md:grid-cols-2 gap-lg items-center">
      <div class="overflow-hidden aspect-[4/3]">
        <img class="w-full h-full object-cover transition-transform duration-700 hover:scale-105" src="<?= img_url('real/storefront.jpg') ?>" alt="Morning ritual at Franca">
      </div>
      <div class="space-y-2">
        <span class="font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em]"><?= e(t('home.atmosphere_label')) ?></span>
        <h3 class="font-headline-sm text-headline-sm"><?= e(t('home.morning_ritual_title')) ?></h3>
        <p class="font-body-md text-on-surface-variant"><?= e(t('home.morning_ritual_text')) ?></p>
      </div>
    </div>
    <div class="reveal-group grid md:grid-cols-2 gap-lg items-center">
      <div class="md:order-2 overflow-hidden aspect-[4/3]">
        <img class="w-full h-full object-cover transition-transform duration-700 hover:scale-105" src="<?= img_url('real/team-kitchen.jpg') ?>" alt="Mindful craft at Franca">
      </div>
      <div class="md:order-1 space-y-2">
        <span class="font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em]"><?= e(t('home.process_label')) ?></span>
        <h3 class="font-headline-sm text-headline-sm"><?= e(t('home.mindful_craft_title')) ?></h3>
        <p class="font-body-md text-on-surface-variant"><?= e(t('home.mindful_craft_text')) ?></p>
      </div>
    </div>
  </div>
</section>

<!-- Newsletter -->
<section class="reveal px-gutter py-2xl bg-secondary-container mb-2xl">
  <div class="max-w-lg mx-auto text-center space-y-md">
    <span class="font-eyebrow text-[11px] text-on-secondary-container uppercase tracking-[0.2em] block"><?= e(t('home.newsletter_title')) ?></span>
    <p class="font-body-md text-on-secondary-container"><?= e(t('home.newsletter_text')) ?></p>
    <form method="post" action="<?= BASE_URL ?>/newsletter.php" class="flex flex-col sm:flex-row gap-sm">
      <?= csrf_field() ?>
      <input class="flex-1 bg-white border-none rounded-full px-md py-3 focus:ring-2 focus:ring-secondary transition-all" placeholder="<?= e(t('home.email_placeholder')) ?>" type="email" name="email" required>
      <button class="btn-lift bg-primary text-on-primary px-lg py-3 rounded-full font-label-md" type="submit"><?= e(t('home.join')) ?></button>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
