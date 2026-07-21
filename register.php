<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/i18n.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/profile.php');
    exit;
}

$errors = [];
$name = $email = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = t('auth.err_session_expired');
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($name === '') $errors[] = t('auth.err_name');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = t('auth.err_email');
        if (strlen($password) < 8) $errors[] = t('auth.err_password_length');
        if ($password !== $confirm) $errors[] = t('auth.err_password_match');

        if (!$errors) {
            $check = db()->prepare('SELECT id FROM users WHERE email = ?');
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = t('auth.err_email_exists');
            } else {
                $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, "customer")');
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $phone]);

                $userStmt = db()->prepare('SELECT * FROM users WHERE id = ?');
                $userStmt->execute([db()->lastInsertId()]);
                login_user($userStmt->fetch());

                header('Location: ' . BASE_URL . '/profile.php');
                exit;
            }
        }
    }
}

$pageTitle = t('auth.create_account_btn') . ' | Franca';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<div class="max-w-md mx-auto py-lg">
  <h1 class="font-headline-md text-headline-md text-primary mb-lg text-center"><?= e(t('auth.create_account_title')) ?></h1>

  <?php if ($errors): ?>
    <div class="bg-error-container text-on-error-container rounded-lg p-3 mb-md text-sm">
      <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" class="bg-surface-container-lowest rounded-xl p-lg space-y-4 editorial-shadow">
    <?= csrf_field() ?>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('auth.full_name')) ?></label>
      <input name="name" type="text" required value="<?= e($name) ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('auth.email')) ?></label>
      <input name="email" type="email" required value="<?= e($email) ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('auth.phone_optional')) ?></label>
      <input name="phone" type="tel" value="<?= e($phone) ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('auth.password')) ?></label>
      <input name="password" type="password" required minlength="8" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('auth.confirm_password')) ?></label>
      <input name="confirm_password" type="password" required minlength="8" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
    <button class="w-full bg-primary text-on-primary py-4 rounded-lg font-label-md uppercase tracking-widest" type="submit"><?= e(t('auth.create_account_btn')) ?></button>
    <p class="font-caption text-on-surface-variant text-center"><?= e(t('auth.already_have_account')) ?> <a class="text-secondary underline" href="<?= BASE_URL ?>/login.php"><?= e(t('auth.log_in')) ?></a></p>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
