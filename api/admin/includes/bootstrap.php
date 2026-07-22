<?php
/**
 * Admin auth + DB bootstrap with NO HTML output.
 * Must be required before any POST handling that might call header()/exit,
 * and before layout_head.php (which does emit HTML).
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/i18n.php';
require_admin();
