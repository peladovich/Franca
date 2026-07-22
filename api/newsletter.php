<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = db()->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->execute([$email]);
        flash('success', 'You\'re on the list! Welcome to the Sunday Circle.');
    } else {
        flash('error', 'Please enter a valid email address.');
    }
}
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/index.php'));
exit;
