<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n.php';

$pageTitle = 'Franca | ' . t('about.eyebrow');
$active = 'about';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="max-w-container-max 2xl:max-w-[1600px] mx-auto mb-xl">
  <div class="reveal-group grid md:grid-cols-2 gap-lg items-center">
    <div class="order-2 md:order-1">
      <span class="font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em] block mb-3"><?= e(t('about.eyebrow')) ?></span>
      <h1 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-primary mb-md"><?= e(t('about.title')) ?></h1>
      <p class="font-body-lg text-on-surface-variant"><?= e(t('about.intro')) ?></p>
    </div>
    <div class="parallax-frame overflow-hidden aspect-[4/5] md:aspect-[4/3] order-1 md:order-2 rounded-photo editorial-shadow">
      <img class="parallax-img" src="<?= img_url('real/flatlay-outside-1.jpg') ?>" alt="Franca table at Plaza Cagancha">
    </div>
  </div>
</section>

<!-- Stats -->
<section class="reveal-group max-w-container-max 2xl:max-w-[1600px] mx-auto mb-2xl grid grid-cols-2 md:grid-cols-4 gap-md">
  <div class="bg-surface-container-lowest rounded-photo p-lg text-center editorial-shadow">
    <p class="font-display-lg-mobile text-display-lg-mobile text-accent-dark">2023</p>
    <p class="font-eyebrow text-[10px] text-on-surface-variant uppercase tracking-[0.15em] mt-1"><?= e(t('about.stat_founded_label')) ?></p>
  </div>
  <div class="bg-surface-container-lowest rounded-photo p-lg text-center editorial-shadow">
    <p class="font-display-lg-mobile text-display-lg-mobile text-accent-dark">15+</p>
    <p class="font-eyebrow text-[10px] text-on-surface-variant uppercase tracking-[0.15em] mt-1"><?= e(t('about.stat_team_label')) ?></p>
  </div>
  <div class="bg-surface-container-lowest rounded-photo p-lg text-center editorial-shadow">
    <p class="font-display-lg-mobile text-display-lg-mobile text-accent-dark">100%</p>
    <p class="font-eyebrow text-[10px] text-on-surface-variant uppercase tracking-[0.15em] mt-1"><?= e(t('about.stat_profit_label')) ?></p>
  </div>
  <div class="bg-surface-container-lowest rounded-photo p-lg text-center editorial-shadow">
    <p class="font-display-lg-mobile text-display-lg-mobile text-accent-dark">3</p>
    <p class="font-eyebrow text-[10px] text-on-surface-variant uppercase tracking-[0.15em] mt-1"><?= e(t('about.stat_causes_label')) ?></p>
  </div>
</section>

<!-- Origin -->
<section class="max-w-container-max 2xl:max-w-[1600px] mx-auto mb-2xl">
  <div class="reveal-group grid md:grid-cols-2 gap-lg items-center">
    <div class="parallax-frame overflow-hidden aspect-[4/3] rounded-photo">
      <img class="parallax-img" src="<?= img_url('real/storefront.jpg') ?>" alt="Franca storefront on Plaza Cagancha">
    </div>
    <div class="space-y-3">
      <span class="font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em]"><?= e(t('about.origin_label')) ?></span>
      <h2 class="font-headline-sm text-headline-sm text-primary"><?= e(t('about.origin_title')) ?></h2>
      <p class="font-body-md text-on-surface-variant"><?= e(t('about.origin_text')) ?></p>
      <blockquote class="border-l-2 border-accent pl-4 py-1 font-headline-sm text-headline-sm text-primary italic"><?= e(t('about.origin_quote')) ?></blockquote>
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
    <div class="menu-item-card bg-surface-container-lowest rounded-photo p-lg flex flex-col items-start gap-3">
      <span class="w-14 h-14 rounded-full bg-accent/15 flex items-center justify-center"><span class="material-symbols-outlined text-accent text-[26px]">volunteer_activism</span></span>
      <span class="font-label-md text-label-md text-primary uppercase tracking-wide"><?= e(t('about.impact_social')) ?></span>
      <p class="font-body-md text-body-md text-on-surface-variant"><?= e(t('about.impact_social_desc')) ?></p>
    </div>
    <div class="menu-item-card bg-surface-container-lowest rounded-photo p-lg flex flex-col items-start gap-3">
      <span class="w-14 h-14 rounded-full bg-accent/15 flex items-center justify-center"><span class="material-symbols-outlined text-accent text-[26px]">eco</span></span>
      <span class="font-label-md text-label-md text-primary uppercase tracking-wide"><?= e(t('about.impact_environment')) ?></span>
      <p class="font-body-md text-body-md text-on-surface-variant"><?= e(t('about.impact_environment_desc')) ?></p>
    </div>
    <div class="menu-item-card bg-surface-container-lowest rounded-photo p-lg flex flex-col items-start gap-3">
      <span class="w-14 h-14 rounded-full bg-accent/15 flex items-center justify-center"><span class="material-symbols-outlined text-accent text-[26px]">pets</span></span>
      <span class="font-label-md text-label-md text-primary uppercase tracking-wide"><?= e(t('about.impact_animal')) ?></span>
      <p class="font-body-md text-body-md text-on-surface-variant"><?= e(t('about.impact_animal_desc')) ?></p>
    </div>
  </div>
</section>

<!-- Team -->
<section class="max-w-container-max 2xl:max-w-[1600px] mx-auto mb-2xl">
  <div class="reveal-group grid md:grid-cols-2 gap-lg items-center">
    <div class="space-y-3 md:order-1">
      <span class="font-eyebrow text-[11px] text-accent-dark uppercase tracking-[0.2em]"><?= e(t('about.team_label')) ?></span>
      <h2 class="font-headline-sm text-headline-sm text-primary"><?= e(t('about.team_title')) ?></h2>
      <p class="font-body-md text-on-surface-variant"><?= e(t('about.team_text')) ?></p>
      <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-secondary-container/50 text-on-secondary-container rounded-full font-label-md text-sm"><span class="material-symbols-outlined text-sm">workspace_premium</span><?= e(t('about.award_badge')) ?></span>
    </div>
    <div class="overflow-hidden aspect-[4/3] md:order-2 rounded-photo">
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
