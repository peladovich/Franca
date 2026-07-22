<?php
// Franca Dining & Coffee - configuration
//
// Every value below can be overridden by an environment variable of the
// same name, so the same file works unmodified both locally (XAMPP
// defaults shown, used whenever a variable isn't set) and on Vercel
// (set these in Project Settings -> Environment Variables).
function env(string $key, string $default): string
{
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306')); // Many cloud MySQL hosts (e.g. Railway) use a non-default port.
define('DB_NAME', env('DB_NAME', 'franca'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// Base URL of the app relative to web root, e.g. "/franca" when running
// under http://localhost/franca/. On Vercel the app is served from the
// domain root, so set BASE_URL="" there.
define('BASE_URL', env('BASE_URL', '/franca'));

// Full public URL of the site, used to build MercadoPago's back_urls and
// notification_url. On localhost this is NOT reachable by MercadoPago's
// servers for webhooks -- see README "Going live with payments" section.
// On Vercel, set SITE_URL to your real https://... deployment URL.
define('SITE_URL', env('SITE_URL', 'http://localhost' . BASE_URL));

// ---------------------------------------------------------------
// MercadoPago (Checkout Pro) — REQUIRED before customers can pay.
// Get your credentials at https://www.mercadopago.com.uy/developers/panel
// Leave MERCADOPAGO_ACCESS_TOKEN empty to keep checkout disabled (fail-closed):
// no order can be placed without a working payment gateway.
// ---------------------------------------------------------------
define('MERCADOPAGO_ACCESS_TOKEN', env('MERCADOPAGO_ACCESS_TOKEN', '')); // Server-side secret. NEVER expose this to the browser.
define('MERCADOPAGO_PUBLIC_KEY', env('MERCADOPAGO_PUBLIC_KEY', ''));   // Optional; only needed if you later add client-side Bricks.
