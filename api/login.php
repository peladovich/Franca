<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/profile.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = t('auth.err_session_expired');
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND role = 'customer'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            login_user($user);
            $redirect = $_GET['redirect'] ?? 'profile.php';
            header('Location: ' . BASE_URL . '/' . ltrim($redirect, '/'));
            exit;
        }
        $errors[] = t('auth.err_invalid_login');
    }
}

$pageTitle = t('auth.log_in') . ' | Franca';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<div class="max-w-md mx-auto py-lg">
  <h1 class="font-headline-md text-headline-md text-primary mb-lg text-center"><?= e(t('auth.welcome_back')) ?></h1>

  <?php if ($errors): ?>
    <div class="bg-error-container text-on-error-container rounded-lg p-3 mb-md text-sm">
      <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" class="bg-surface-container-lowest rounded-xl p-lg space-y-4 editorial-shadow">
    <?= csrf_field() ?>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('auth.email')) ?></label>
      <input name="email" type="email" required value="<?= e($email) ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('auth.password')) ?></label>
      <input name="password" type="password" required class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
    <button class="btn-lift w-full bg-accent text-on-accent py-4 rounded-full font-label-md uppercase tracking-widest" type="submit"><?= e(t('auth.log_in')) ?></button>
    <p class="font-caption text-on-surface-variant text-center"><?= e(t('auth.new_to_franca')) ?> <a class="text-secondary underline" href="<?= BASE_URL ?>/register.php"><?= e(t('auth.create_account_link')) ?></a></p>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
