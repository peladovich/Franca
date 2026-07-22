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
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,500;0,8..60,600;1,8..60,400&display=swap" rel="stylesheet">
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
        "on-secondary-fixed": "#082014", "on-secondary-fixed-variant": "#344c3e", "inverse-on-surface": "#f3f0f0"
      },
      borderRadius: { DEFAULT: "0.25rem", lg: "0.75rem", xl: "1.25rem", full: "9999px" },
      spacing: { md: "24px", gutter: "24px", xs: "4px", sm: "12px", xl: "80px", base: "8px", "container-max": "1200px", lg: "48px" },
      fontFamily: {
        "headline-sm": ['"Source Serif 4"'], "caption": ["Plus Jakarta Sans"], "body-md": ["Plus Jakarta Sans"],
        "display-lg": ['"Source Serif 4"'], "label-md": ["Plus Jakarta Sans"], "display-lg-mobile": ['"Source Serif 4"'],
        "headline-md": ['"Source Serif 4"'], "body-lg": ["Plus Jakarta Sans"]
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
  body { min-height: max(884px, 100dvh); -webkit-tap-highlight-color: transparent; scroll-behavior: smooth; }
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
</style>
<?= $extraHead ?? '' ?>
</head>
<body class="bg-background text-on-background font-body-md antialiased selection:bg-secondary-container">
