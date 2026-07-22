<?php
/**
 * Single serverless-function entrypoint.
 *
 * Vercel's Hobby (free) plan caps a deployment at 12 Serverless Functions,
 * but this app has 20+ page scripts. Instead of one function per file
 * (which a naive "*.php" functions glob would create), every request is
 * rewritten to this one file (see vercel.json), which dispatches to the
 * real page script by require()'ing it -- each page's own __DIR__-relative
 * requires still resolve correctly, since PHP's __DIR__ is fixed per
 * source file regardless of how it was included.
 *
 * The target is looked up in an explicit whitelist (not built from the
 * request path directly) so this can never be tricked into require()'ing
 * an arbitrary file.
 */
$route = trim($_GET['__route'] ?? '', '/');
if ($route === '') {
    $route = 'index.php';
}

$routes = [
    'index.php' => 'index.php',
    'menu.php' => 'menu.php',
    'dish.php' => 'dish.php',
    'cart.php' => 'cart.php',
    'reservations.php' => 'reservations.php',
    'login.php' => 'login.php',
    'register.php' => 'register.php',
    'logout.php' => 'logout.php',
    'profile.php' => 'profile.php',
    'order-confirmation.php' => 'order-confirmation.php',
    'newsletter.php' => 'newsletter.php',
    'admin' => 'admin/index.php',
    'admin/index.php' => 'admin/index.php',
    'admin/login.php' => 'admin/login.php',
    'admin/logout.php' => 'admin/logout.php',
    'admin/categories.php' => 'admin/categories.php',
    'admin/menu-items.php' => 'admin/menu-items.php',
    'admin/menu-item-edit.php' => 'admin/menu-item-edit.php',
    'admin/orders.php' => 'admin/orders.php',
    'admin/reservations.php' => 'admin/reservations.php',
    'admin/settings.php' => 'admin/settings.php',
    'admin/users.php' => 'admin/users.php',
    'webhook/mercadopago.php' => 'webhook/mercadopago.php',
];

$target = $routes[$route] ?? null;

if ($target === null) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo '404 Not Found';
    exit;
}

// Normalize REQUEST_URI to the clean public path (stripping the internal
// __route param) so code in the dispatched page -- e.g. includes/i18n.php's
// language switcher, which builds links from $_SERVER['REQUEST_URI'] --
// sees the same URL the visitor actually requested, not Vercel's rewrite
// target.
unset($_GET['__route']);
$qs = http_build_query($_GET);
$_SERVER['REQUEST_URI'] = '/' . $route . ($qs ? '?' . $qs : '');

require __DIR__ . '/' . $target;
