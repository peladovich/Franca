<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n.php';

$pageTitle = 'Franca | ' . t('about.eyebrow');
$active = 'about';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<header class="reveal mb-2xl text-center md:text-left max-w-3xl">
  <span class="font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em] block mb-3"><?= e(t('about.eyebrow')) ?></span>
  <h1 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-primary mb-md"><?= e(t('about.title')) ?></h1>
  <p class="font-body-lg text-on-surface-variant"><?= e(t('about.intro')) ?></p>
</header>

<!-- Origin -->
<section class="max-w-container-max 2xl:max-w-[1600px] mx-auto mb-2xl">
  <div class="reveal-group grid md:grid-cols-2 gap-lg items-center">
    <div class="parallax-frame overflow-hidden aspect-[4/3]">
      <img class="parallax-img" src="<?= img_url('real/storefront.jpg') ?>" alt="Franca storefront on Plaza Cagancha">
    </div>
    <div class="space-y-2">
      <span class="font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em]"><?= e(t('about.origin_label')) ?></span>
      <h2 class="font-headline-sm text-headline-sm text-primary"><?= e(t('about.origin_title')) ?></h2>
      <p class="font-body-md text-on-surface-variant"><?= e(t('about.origin_text')) ?></p>
    </div>
  </div>
</section>

<!-- Model -->
<section class="reveal full-bleed mb-2xl bg-gradient-to-br from-primary via-primary to-on-primary-fixed px-gutter md:px-2xl py-2xl">
  <div class="max-w-2xl mx-auto">
    <span class="font-eyebrow text-[11px] text-white/60 uppercase tracking-[0.2em] block mb-3"><?= e(t('about.model_label')) ?></span>
    <h2 class="font-headline-md text-headline-md text-white mb-md"><?= e(t('about.model_title')) ?></h2>
    <p class="font-body-lg text-white/90"><?= e(t('about.model_text')) ?></p>
  </div>
</section>

<!-- Impact -->
<section class="max-w-container-max 2xl:max-w-[1600px] mx-auto mb-2xl">
  <span class="reveal font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em] block mb-3"><?= e(t('about.impact_label')) ?></span>
  <h2 class="reveal font-headline-md text-headline-md text-primary mb-md max-w-2xl"><?= e(t('about.impact_title')) ?></h2>
  <p class="reveal font-body-md text-on-surface-variant mb-lg max-w-2xl"><?= e(t('about.impact_text')) ?></p>
  <div class="reveal-group grid grid-cols-1 sm:grid-cols-3 gap-md">
    <div class="bg-surface-container-lowest rounded-photo p-lg flex flex-col items-start gap-3 editorial-shadow">
      <span class="material-symbols-outlined text-accent text-[28px]">volunteer_activism</span>
      <span class="font-label-md text-label-md text-primary uppercase tracking-wide"><?= e(t('about.impact_social')) ?></span>
    </div>
    <div class="bg-surface-container-lowest rounded-photo p-lg flex flex-col items-start gap-3 editorial-shadow">
      <span class="material-symbols-outlined text-accent text-[28px]">eco</span>
      <span class="font-label-md text-label-md text-primary uppercase tracking-wide"><?= e(t('about.impact_environment')) ?></span>
    </div>
    <div class="bg-surface-container-lowest rounded-photo p-lg flex flex-col items-start gap-3 editorial-shadow">
      <span class="material-symbols-outlined text-accent text-[28px]">pets</span>
      <span class="font-label-md text-label-md text-primary uppercase tracking-wide"><?= e(t('about.impact_animal')) ?></span>
    </div>
  </div>
</section>

<!-- Team -->
<section class="max-w-container-max 2xl:max-w-[1600px] mx-auto mb-2xl">
  <div class="reveal-group grid md:grid-cols-2 gap-lg items-center">
    <div class="space-y-2 md:order-1">
      <span class="font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em]"><?= e(t('about.team_label')) ?></span>
      <h2 class="font-headline-sm text-headline-sm text-primary"><?= e(t('about.team_title')) ?></h2>
      <p class="font-body-md text-on-surface-variant"><?= e(t('about.team_text')) ?></p>
    </div>
    <div class="overflow-hidden aspect-[4/3] md:order-2">
      <img class="vibe-img-el w-full h-full object-cover" src="<?= img_url('real/team-full.jpg') ?>" alt="The Franca team">
    </div>
  </div>
</section>

<!-- CTA -->
<section class="reveal px-gutter py-2xl bg-gradient-to-br from-secondary-container via-secondary-container to-background mb-2xl text-center">
  <div class="max-w-lg mx-auto space-y-md">
    <h2 class="font-headline-md text-headline-md text-on-secondary-container"><?= e(t('about.cta_title')) ?></h2>
    <p class="font-body-md text-on-secondary-container"><?= e(t('about.cta_text')) ?></p>
    <div class="flex flex-col sm:flex-row gap-sm justify-center">
      <a href="<?= BASE_URL ?>/menu.php" class="btn-lift bg-primary text-on-primary px-lg py-3 rounded-full font-label-md"><?= e(t('home.view_menu')) ?></a>
      <a href="<?= BASE_URL ?>/reservations.php" class="btn-lift bg-white text-primary border border-primary/20 px-lg py-3 rounded-full font-label-md"><?= e(t('home.book_table')) ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
