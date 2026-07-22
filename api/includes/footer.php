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
        <a class="font-body-md text-body-md text-on-surface-variant hover:text-primary" href="<?= BASE_URL ?>/about.php"><?= e(t('about.eyebrow')) ?></a>
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
  var targets = document.querySelectorAll('.reveal, .reveal-group, .reveal-wipe');
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

// Scroll parallax for .parallax-img: drifts within its frame based on how far
// the frame has scrolled through the viewport. Always runs, same reasoning
// as the hero carousel timer -- a gentle 60px drift isn't the kind of motion
// prefers-reduced-motion is meant to suppress, and users on this site were
// getting a silently-frozen effect when that OS setting was on.
(function () {
  var imgs = Array.prototype.slice.call(document.querySelectorAll('.parallax-img'));
  if (!imgs.length) return;
  var ticking = false;
  function update() {
    imgs.forEach(function (img) {
      var rect = img.parentElement.getBoundingClientRect();
      var progress = (window.innerHeight - rect.top) / (window.innerHeight + rect.height);
      var offset = (Math.min(1, Math.max(0, progress)) - 0.5) * 60;
      img.style.transform = 'translateY(' + offset.toFixed(1) + 'px)';
    });
    ticking = false;
  }
  window.addEventListener('scroll', function () {
    if (!ticking) { requestAnimationFrame(update); ticking = true; }
  }, { passive: true });
  update();
})();

// Count-up numbers: [data-count-to="15" data-count-suffix="+"] animates its
// own text from 0 to the target once it scrolls into view, then stops.
(function () {
  var counters = document.querySelectorAll('[data-count-to]');
  if (!counters.length) return;
  function animate(el) {
    var target = parseInt(el.getAttribute('data-count-to'), 10) || 0;
    var suffix = el.getAttribute('data-count-suffix') || '';
    var duration = 1100;
    var start = null;
    function step(ts) {
      if (start === null) start = ts;
      var progress = Math.min(1, (ts - start) / duration);
      var eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.round(eased * target) + suffix;
      if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  if (!('IntersectionObserver' in window)) {
    counters.forEach(animate);
  } else {
    var counterObserver = new IntersectionObserver(function (entries, obs) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animate(entry.target);
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(function (el) { counterObserver.observe(el); });
  }
})();
</script>
</body>
</html>
