<?php
// Franca Dining & Coffee - configuration
// Edit these values to match your MySQL setup (XAMPP defaults shown).
define('DB_HOST', 'localhost');
define('DB_NAME', 'franca');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL of the app relative to web root, e.g. "/franca" when running
// under http://localhost/franca/. Leave as '' if the app is the web root.
define('BASE_URL', '/franca');

// Full public URL of the site, used to build MercadoPago's back_urls and
// notification_url. On localhost this is NOT reachable by MercadoPago's
// servers for webhooks — see README "Going live with payments" section.
define('SITE_URL', 'http://localhost' . BASE_URL);

// ---------------------------------------------------------------
// MercadoPago (Checkout Pro) — REQUIRED before customers can pay.
// Get your credentials at https://www.mercadopago.com.uy/developers/panel
// Leave MERCADOPAGO_ACCESS_TOKEN empty to keep checkout disabled (fail-closed):
// no order can be placed without a working payment gateway.
// ---------------------------------------------------------------
define('MERCADOPAGO_ACCESS_TOKEN', ''); // Server-side secret. NEVER expose this to the browser.
define('MERCADOPAGO_PUBLIC_KEY', '');   // Optional; only needed if you later add client-side Bricks.
