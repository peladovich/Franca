<?php $active = $active ?? ''; ?>
</main>
<footer class="hidden md:block w-full py-xl px-gutter bg-surface-container-low border-t border-outline-variant/30">
  <div class="max-w-container-max 2xl:max-w-[1600px] mx-auto flex flex-col md:flex-row justify-between items-start gap-lg">
    <div class="flex flex-col gap-2">
      <span class="font-display-lg text-[32px] text-primary">Franca</span>
      <p class="font-caption text-caption text-on-surface-variant max-w-xs"><?= e(t('footer.tagline')) ?></p>
    </div>
    <div class="flex flex-wrap gap-8 md:gap-16">
      <nav class="flex flex-col gap-2">
        <span class="font-label-md text-label-md text-primary font-bold"><?= e(t('footer.visit_us')) ?></span>
        <a class="font-body-md text-body-md text-on-surface-variant hover:text-primary" href="<?= BASE_URL ?>/reservations.php"><?= e(t('footer.location_hours')) ?></a>
        <a class="font-body-md text-body-md text-on-surface-variant hover:text-primary" href="<?= BASE_URL ?>/menu.php"><?= e(t('nav.menu')) ?></a>
      </nav>
      <nav class="flex flex-col gap-2">
        <span class="font-label-md text-label-md text-primary font-bold"><?= e(t('footer.account')) ?></span>
        <a class="font-body-md text-body-md text-on-surface-variant hover:text-primary" href="<?= BASE_URL ?>/profile.php"><?= e(t('nav.profile')) ?></a>
        <a class="font-body-md text-body-md text-on-surface-variant hover:text-primary" href="<?= BASE_URL ?>/admin/login.php"><?= e(t('footer.staff_login')) ?></a>
      </nav>
    </div>
    <p class="font-caption text-caption text-on-surface-variant"><?= e(t('footer.rights', ['year' => date('Y')])) ?></p>
  </div>
</footer>
<!-- Mobile bottom nav -->
<nav class="md:hidden fixed bottom-0 left-0 w-full flex justify-around items-center px-4 py-3 pb-safe bg-surface-container-lowest z-50 rounded-t-xl shadow-lg">
  <a href="<?= BASE_URL ?>/index.php" class="flex flex-col items-center justify-center <?= $active === 'home' ? 'bg-secondary-container text-on-secondary-container rounded-full px-4 py-1' : 'text-on-surface-variant' ?>">
    <span class="material-symbols-outlined" style="<?= $active === 'home' ? "font-variation-settings: 'FILL' 1;" : '' ?>">home</span>
    <span class="font-label-md text-[10px]"><?= e(t('nav.home')) ?></span>
  </a>
  <a href="<?= BASE_URL ?>/menu.php" class="flex flex-col items-center justify-center <?= $active === 'menu' ? 'bg-secondary-container text-on-secondary-container rounded-full px-4 py-1' : 'text-on-surface-variant' ?>">
    <span class="material-symbols-outlined">restaurant_menu</span>
    <span class="font-label-md text-[10px]"><?= e(t('nav.menu')) ?></span>
  </a>
  <a href="<?= BASE_URL ?>/reservations.php" class="flex flex-col items-center justify-center <?= $active === 'reservations' ? 'bg-secondary-container text-on-secondary-container rounded-full px-4 py-1' : 'text-on-surface-variant' ?>">
    <span class="material-symbols-outlined">event_available</span>
    <span class="font-label-md text-[10px]"><?= e(t('nav.book')) ?></span>
  </a>
  <a href="<?= BASE_URL ?>/<?= current_user() ? 'profile.php' : 'login.php' ?>" class="flex flex-col items-center justify-center <?= $active === 'profile' ? 'bg-secondary-container text-on-secondary-container rounded-full px-4 py-1' : 'text-on-surface-variant' ?>">
    <span class="material-symbols-outlined">person</span>
    <span class="font-label-md text-[10px]"><?= e(t('nav.profile')) ?></span>
  </a>
</nav>
<script>
(function () {
  var targets = document.querySelectorAll('.reveal, .reveal-group');
  if (!targets.length) return;
  if (!('IntersectionObserver' in window)) {
    targets.forEach(function (el) { el.classList.add('is-visible'); });
    return;
  }
  var observer = new IntersectionObserver(function (entries, obs) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
  targets.forEach(function (el) { observer.observe(el); });
})();

// .full-bleed's CSS (width:100vw + calc(50%-50vw) margins) is off by half the
// scrollbar's width on desktop browsers with classic (non-overlay) scrollbars,
// since vw includes the scrollbar but the layout box doesn't. Nudge it exact.
(function () {
  var els = document.querySelectorAll('.full-bleed');
  if (!els.length) return;
  function apply() {
    var vw = document.documentElement.clientWidth;
    els.forEach(function (el) {
      var currentMarginLeft = parseFloat(getComputedStyle(el).marginLeft) || 0;
      var correction = -el.getBoundingClientRect().left;
      el.style.width = vw + 'px';
      el.style.marginLeft = (currentMarginLeft + correction) + 'px';
    });
  }
  apply();
  var resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(apply, 150);
  });
})();
</script>
</body>
</html>
