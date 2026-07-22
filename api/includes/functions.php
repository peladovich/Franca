<?php
require_once __DIR__ . '/db.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function money(float $amount): string
{
    if ($amount <= 0) {
        return 'Consultar';
    }
    return '$U ' . number_format($amount, 0, ',', '.');
}

function get_setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT `key`, `value` FROM settings') as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }
    return $cache[$key] ?? $default;
}

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

// --- Session-based shopping bag: [menu_item_id => quantity] ---
function cart_get(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_add(int $menuItemId, int $qty = 1): void
{
    $_SESSION['cart'][$menuItemId] = ($_SESSION['cart'][$menuItemId] ?? 0) + $qty;
}

function cart_remove(int $menuItemId): void
{
    unset($_SESSION['cart'][$menuItemId]);
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

function cart_count(): int
{
    return array_sum(cart_get());
}

function img_url(?string $filename): string
{
    if (!$filename) {
        return BASE_URL . '/assets/img/home-hero.jpg';
    }
    return BASE_URL . '/assets/img/' . $filename;
}
