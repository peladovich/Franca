<?php
/**
 * Minimal i18n: session-persisted locale + t() lookup helper.
 * Require this AFTER auth.php (which starts the session).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const SUPPORTED_LOCALES = ['es', 'en'];
const DEFAULT_LOCALE = 'es';

// ?lang=xx switches the language, persists it in the session, then redirects
// back to the same URL with the lang param stripped (keeps URLs clean).
if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LOCALES, true)) {
    $_SESSION['locale'] = $_GET['lang'];
    $params = $_GET;
    unset($params['lang']);
    $qs = http_build_query($params);
    $path = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $path . ($qs ? '?' . $qs : ''));
    exit;
}

$GLOBALS['__locale'] = in_array($_SESSION['locale'] ?? '', SUPPORTED_LOCALES, true)
    ? $_SESSION['locale']
    : DEFAULT_LOCALE;

$GLOBALS['__translations'] = require __DIR__ . '/../lang/' . $GLOBALS['__locale'] . '.php';

function t(string $key, array $vars = []): string
{
    $str = $GLOBALS['__translations'][$key] ?? $key;
    foreach ($vars as $k => $v) {
        $str = str_replace('{' . $k . '}', (string) $v, $str);
    }
    return $str;
}

function current_locale(): string
{
    return $GLOBALS['__locale'];
}

/** Builds a "switch to this language" link preserving the current URL. */
function lang_switch_url(string $locale): string
{
    $params = $_GET;
    $params['lang'] = $locale;
    $path = strtok($_SERVER['REQUEST_URI'], '?');
    return $path . '?' . http_build_query($params);
}

/**
 * Reads a DB-sourced field with locale awareness: falls back to the base
 * (Spanish) column whenever the current locale isn't English or the
 * "_en" column is empty, so a missing translation never renders blank.
 */
function mi_field(array $row, string $field): string
{
    if (current_locale() === 'en') {
        $enValue = $row[$field . '_en'] ?? '';
        if ($enValue !== '') {
            return $enValue;
        }
    }
    return $row[$field] ?? '';
}

/** service_mode enum ('dine-in'|'takeaway'|'delivery') -> translated label. */
function service_mode_label(string $mode): string
{
    return t('home.' . str_replace('-', '_', $mode));
}

/** orders.status / reservations.status enum -> translated label. */
function status_label(string $status): string
{
    return t('status.' . $status);
}
