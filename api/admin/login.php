<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/i18n.php';

if (is_admin()) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = t('admin.err_session_expired');
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            login_user($user);
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        }
        $errors[] = t('admin.err_invalid_login');
    }
}

$pageTitle = 'Admin Login | Franca';
require __DIR__ . '/../includes/head.php';
?>

<div class="min-h-screen flex flex-col items-center justify-center px-gutter">
  <div class="absolute top-6 right-6"><?= lang_switcher_html() ?></div>
  <div class="max-w-sm w-full">
    <a href="<?= BASE_URL ?>/index.php" class="flex items-center justify-center gap-1.5 font-wordmark font-extrabold text-[26px] leading-none tracking-tight text-primary mb-2">
      <img src="<?= BASE_URL ?>/assets/img/brand/logo-mark.png" alt="" class="w-6 h-6 object-contain">FRANCA
    </a>
    <p class="font-eyebrow text-[11px] text-on-surface-variant text-center uppercase tracking-[0.2em] mb-lg"><?= e(t('admin.staff_login')) ?></p>

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
      <button class="w-full bg-accent text-on-accent py-4 rounded-full font-label-md uppercase tracking-widest" type="submit"><?= e(t('auth.log_in')) ?></button>
      <p class="text-center"><a class="font-caption text-on-surface-variant underline" href="<?= BASE_URL ?>/index.php"><?= e(t('admin.back_to_site')) ?></a></p>
    </form>
  </div>
</div>
</body>
</html>
