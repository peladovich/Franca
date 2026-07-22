<?php
/**
 * Shared <head> partial: fonts, Tailwind CDN + Franca design tokens.
 * Expects optional $pageTitle to be set before include.
 */
$pageTitle = $pageTitle ?? get_setting('site_name', 'Franca Dining & Coffee');
$htmlLang = function_exists('current_locale') ? current_locale() : 'es';
?><!DOCTYPE html>
<html lang="<?= e($htmlLang) ?>" class="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?= e($pageTitle) ?></title>
<link rel="icon" href="<?= BASE_URL ?>/assets/img/brand/logo.jpg" type="image/jpeg">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,500;0,8..60,600;1,8..60,400&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<script id="tailwind-config">
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "primary-fixed": "#ffdbd0", "on-secondary-container": "#506859", "on-secondary": "#ffffff",
        "on-tertiary-fixed-variant": "#484743", "background": "#fcf9f8", "on-primary-fixed": "#2c160e",
        "surface": "#fcf9f8", "on-primary-container": "#d4ada1", "secondary-container": "#cbe6d4",
        "error-container": "#ffdad6", "tertiary-fixed-dim": "#c9c6c1", "on-tertiary-fixed": "#1c1c19",
        "on-tertiary-container": "#b8b5b1", "on-background": "#1b1c1c", "tertiary": "#31312e",
        "surface-container-low": "#f6f3f2", "secondary": "#4c6455", "secondary-fixed": "#cee9d6",
        "on-surface": "#1b1c1c", "on-primary": "#ffffff", "secondary-fixed-dim": "#b2cdbb",
        "on-error": "#ffffff", "error": "#ba1a1a", "tertiary-container": "#484744",
        "inverse-primary": "#e7bdb1", "outline-variant": "#d4c3be", "outline": "#827470",
        "surface-container-highest": "#e4e2e1", "inverse-surface": "#303030", "surface-variant": "#e4e2e1",
        "on-primary-fixed-variant": "#5d4037", "primary-container": "#5d4037", "primary": "#442a22",
        "surface-tint": "#77574d", "primary-fixed-dim": "#e7bdb1", "surface-container": "#f0eded",
        "surface-dim": "#dcd9d9", "on-error-container": "#93000a", "tertiary-fixed": "#e6e2dd",
        "surface-container-high": "#eae7e7", "surface-container-lowest": "#ffffff",
        "on-surface-variant": "#504441", "on-tertiary": "#ffffff", "surface-bright": "#fcf9f8",
        "on-secondary-fixed": "#082014", "on-secondary-fixed-variant": "#344c3e", "inverse-on-surface": "#f3f0f0",
        "accent": "#F3542C", "accent-dark": "#C2431F", "on-accent": "#2c160e"
      },
      borderRadius: { DEFAULT: "0.25rem", lg: "0.75rem", xl: "1.25rem", photo: "6px", full: "9999px" },
      spacing: { md: "24px", gutter: "24px", xs: "4px", sm: "12px", xl: "80px", "2xl": "112px", base: "8px", "container-max": "1200px", lg: "48px" },
      fontFamily: {
        "headline-sm": ['"Source Serif 4"'], "caption": ["Plus Jakarta Sans"], "body-md": ["Plus Jakarta Sans"],
        "display-lg": ['"Source Serif 4"'], "label-md": ["Plus Jakarta Sans"], "display-lg-mobile": ['"Source Serif 4"'],
        "headline-md": ['"Source Serif 4"'], "body-lg": ["Plus Jakarta Sans"], "wordmark": ["Plus Jakarta Sans"],
        "eyebrow": ['"JetBrains Mono"']
      },
      fontSize: {
        "headline-sm": ["24px", { lineHeight: "32px", fontWeight: "500" }],
        "caption": ["12px", { lineHeight: "16px", fontWeight: "400" }],
        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
        "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "600" }],
        "label-md": ["14px", { lineHeight: "20px", letterSpacing: "0.05em", fontWeight: "600" }],
        "display-lg-mobile": ["36px", { lineHeight: "44px", letterSpacing: "-0.02em", fontWeight: "600" }],
        "headline-md": ["32px", { lineHeight: "40px", fontWeight: "500" }],
        "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }]
      }
    }
  }
}
</script>
<style>
  html, body { overflow-x: hidden; }
  body { min-height: max(884px, 100dvh); -webkit-tap-highlight-color: transparent; scroll-behavior: smooth; }
  /* True edge-to-edge breakout regardless of viewport width, for sections
     nested inside <main>'s max-w-container-max column. Safe against the
     phantom-horizontal-scroll issue vw-based breakouts can cause because
     html/body clip any overflow above (a deliberate, scoped safety net --
     nothing on this site relies on horizontal scroll at the page level). */
  .full-bleed { width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); }
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
  .hide-scrollbar::-webkit-scrollbar { display: none; }
  .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  .editorial-shadow { box-shadow: 0 4px 20px rgba(93,64,55,0.08); }
  .leader-dots { flex-grow: 1; margin: 0 12px; border-bottom: 2px dotted #d4c3be; position: relative; top: -6px; }
  .menu-item-card {
    box-shadow: 0 1px 3px rgba(93,64,55,0.06), 0 1px 2px rgba(93,64,55,0.05);
    border: 1px solid rgba(212,195,190,0.35);
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s ease, border-color 0.3s ease;
  }
  .menu-item-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 28px rgba(93,64,55,0.14);
    border-color: rgba(76,100,85,0.35);
  }

  /* Category filter pills (menu page) */
  .cat-pill { transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease; }
  .cat-pill.is-active { box-shadow: 0 4px 10px rgba(68,42,34,0.25); }
  .cat-pill:not(.is-active):hover { border-color: #442a22; color: #442a22; }

  /* Buttons that lift on hover */
  .btn-lift { transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease; }
  .btn-lift:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(68,42,34,0.22); }
  .btn-lift:active:not(:disabled) { transform: translateY(0); }

  :focus-visible { outline: 2px solid #4c6455; outline-offset: 2px; border-radius: 4px; }

  /* Scroll-reveal: elements fade + rise into place as they enter the viewport.
     .reveal-group staggers its direct children via transition-delay. */
  .reveal { opacity: 0; transform: translateY(28px); transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1); }
  .reveal.is-visible { opacity: 1; transform: translateY(0); }
  .reveal-group.is-visible > * { opacity: 1; transform: translateY(0); }
  .reveal-group > * { opacity: 0; transform: translateY(28px); transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1); }
  .reveal-group.is-visible > *:nth-child(1) { transition-delay: 0s; }
  .reveal-group.is-visible > *:nth-child(2) { transition-delay: 0.08s; }
  .reveal-group.is-visible > *:nth-child(3) { transition-delay: 0.16s; }
  .reveal-group.is-visible > *:nth-child(4) { transition-delay: 0.24s; }
  .reveal-group.is-visible > *:nth-child(5) { transition-delay: 0.32s; }
  .reveal-group.is-visible > *:nth-child(6) { transition-delay: 0.4s; }
  @media (prefers-reduced-motion: reduce) {
    .reveal, .reveal-group > * { opacity: 1; transform: none; transition: none; }
  }

  /* Underline-draw link, used for inline editorial links (Culto/Flora-style). */
  .link-underline { position: relative; }
  .link-underline::after {
    content: ""; position: absolute; left: 0; bottom: -2px; width: 100%; height: 1px;
    background: currentColor; transform: scaleX(0); transform-origin: right; transition: transform 0.4s cubic-bezier(0.16,1,0.3,1);
  }
  .link-underline:hover::after { transform: scaleX(1); transform-origin: left; }

  .scroll-cue { animation: scroll-cue-bounce 1.8s ease-in-out infinite; }
  @keyframes scroll-cue-bounce { 0%, 100% { transform: translateY(0); opacity: 0.6; } 50% { transform: translateY(8px); opacity: 1; } }

  /* Hero photo carousel: stacked slides cross-fade via opacity, plus a slow
     Ken Burns zoom on whichever slide is active. The crossfade timing (auto-
     advance) always runs; only the zoom is skipped under reduced-motion,
     since a slow opacity fade isn't the kind of motion that spec targets. */
  .hero-slide { opacity: 0; transition: opacity 1.2s ease; transform: scale(1.06); }
  .hero-slide.is-active { opacity: 1; animation: hero-kenburns 6s ease-out forwards; }
  @keyframes hero-kenburns { from { transform: scale(1.06); } to { transform: scale(1); } }
  @media (prefers-reduced-motion: reduce) {
    .hero-slide.is-active { animation: none; transform: scale(1); }
  }

  /* Progress-bar slide indicators (Instagram-story style): the fill animates
     across the same 5s window as the JS auto-advance timer, and restarts
     cleanly whenever goTo() toggles the is-active class off and back on. */
  .hero-progress { position: relative; width: 32px; height: 3px; border-radius: 9999px; background: rgba(255,255,255,0.3); overflow: hidden; }
  .hero-progress-fill { position: absolute; inset: 0; width: 0%; background: #fff; border-radius: 9999px; }
  .hero-progress.is-active .hero-progress-fill { animation: hero-progress-fill 5s linear forwards; }
  @keyframes hero-progress-fill { from { width: 0%; } to { width: 100%; } }
  @media (prefers-reduced-motion: reduce) {
    .hero-progress.is-active .hero-progress-fill { animation: none; width: 100%; }
  }

  /* Marquee ticker: a duplicated track scrolls left forever, so the loop is
     seamless. Always animates (hover/focus still pauses it) -- like the hero
     carousel's slide timer, this always runs regardless of reduced-motion so
     it doesn't silently sit frozen for anyone with that OS setting on. */
  .marquee-track { display: flex; width: max-content; animation: marquee-scroll 28s linear infinite; }
  .marquee-track:hover, .marquee-track:focus-within { animation-play-state: paused; }
  @keyframes marquee-scroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }

  /* Our Vibe photos: a slow zoom-settle as each panel reveals, plus its own hover zoom. */
  .vibe-img-el { transform: scale(1.15); transition: transform 1.2s cubic-bezier(0.16,1,0.3,1); }
  .reveal-group.is-visible .vibe-img-el { transform: scale(1); }
  .reveal-group.is-visible .vibe-img-el:hover { transform: scale(1.06); }
  @media (prefers-reduced-motion: reduce) {
    .vibe-img-el, .reveal-group.is-visible .vibe-img-el { transform: none; transition: none; }
  }

  /* Scroll parallax: the image sits taller than its frame and drifts as the
     page scrolls past it (see footer.php for the scroll-driven JS). Runs
     unconditionally (see footer.php), so stays sized for movement always. */
  .parallax-frame { position: relative; overflow: hidden; }
  .parallax-img { position: absolute; left: 0; top: -12%; width: 100%; height: 124%; object-fit: cover; will-change: transform; }
</style>
<?= $extraHead ?? '' ?>
</head>
<body class="bg-background text-on-background font-body-md antialiased selection:bg-secondary-container">
