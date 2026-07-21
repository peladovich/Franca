<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Your session expired, please try again.';
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
        $errors[] = 'Incorrect email or password.';
    }
}

$pageTitle = 'Admin Login | Franca';
require __DIR__ . '/../includes/head.php';
?>

<div class="min-h-screen flex items-center justify-center px-gutter">
  <div class="max-w-sm w-full">
    <h1 class="font-display-lg-mobile text-display-lg-mobile text-primary text-center mb-2">FRANCA</h1>
    <p class="font-label-md text-on-surface-variant text-center uppercase tracking-widest mb-lg">Staff Login</p>

    <?php if ($errors): ?>
      <div class="bg-error-container text-on-error-container rounded-lg p-3 mb-md text-sm">
        <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="bg-surface-container-lowest rounded-xl p-lg space-y-4 editorial-shadow">
      <?= csrf_field() ?>
      <div>
        <label class="font-label-md text-primary block mb-1">Email</label>
        <input name="email" type="email" required value="<?= e($email) ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
      </div>
      <div>
        <label class="font-label-md text-primary block mb-1">Password</label>
        <input name="password" type="password" required class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
      </div>
      <button class="w-full bg-primary text-on-primary py-4 rounded-lg font-label-md uppercase tracking-widest" type="submit">Log In</button>
      <p class="text-center"><a class="font-caption text-on-surface-variant underline" href="<?= BASE_URL ?>/index.php">Back to site</a></p>
    </form>
  </div>
</div>
</body>
</html>
