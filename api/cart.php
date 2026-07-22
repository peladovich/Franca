<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/payments/mercadopago.php';
require_once __DIR__ . '/includes/i18n.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
    flash('error', t('cart.err_session_expired'));
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $menuItemId = (int) ($_POST['menu_item_id'] ?? 0);
    if ($menuItemId > 0) {
        cart_add($menuItemId, 1);
        flash('success', t('cart.added'));
    }
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/menu.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remove') {
    cart_remove((int) ($_POST['menu_item_id'] ?? 0));
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkout') {
    $cart = cart_get();
    if (!$cart) {
        flash('error', t('cart.err_empty'));
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    // Fail closed: with no working payment gateway, no order may be created at all.
    if (!mp_configured()) {
        flash('error', t('cart.err_payment_unavailable'));
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    $user = current_user();
    $guestName = trim($_POST['guest_name'] ?? '');
    $guestPhone = trim($_POST['guest_phone'] ?? '');
    $guestEmail = trim($_POST['guest_email'] ?? '');
    $serviceMode = in_array($_POST['service_mode'] ?? '', ['dine-in', 'takeaway'], true)
        ? $_POST['service_mode'] : 'dine-in';

    if (!$user && $guestName === '') {
        flash('error', t('cart.err_name_required'));
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }
    if (!$user && $guestEmail !== '' && !filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
        flash('error', t('cart.err_invalid_email'));
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = db()->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $rows = $stmt->fetchAll();
    $byId = [];
    foreach ($rows as $r) $byId[$r['id']] = $r;

    $total = 0;
    $itemLines = [];
    foreach ($cart as $id => $qty) {
        if (!isset($byId[$id])) continue;
        $total += $byId[$id]['price'] * $qty;
        $itemLines[] = ['id' => $id, 'name' => $byId[$id]['name'], 'quantity' => $qty, 'unit_price' => $byId[$id]['price']];
    }
    if (!$itemLines) {
        flash('error', t('cart.err_empty'));
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    $db = db();
    $db->beginTransaction();
    // Order starts unpaid — kitchen status stays 'pending' and the admin panel
    // will not allow it to advance past that until payment_status = 'paid'.
    $ins = $db->prepare("INSERT INTO orders (user_id, guest_name, guest_phone, service_mode, status, payment_status, total) VALUES (?, ?, ?, ?, 'pending', 'pending', ?)");
    $ins->execute([$user['id'] ?? null, $user ? null : $guestName, $user ? null : $guestPhone, $serviceMode, $total]);
    $orderId = (int) $db->lastInsertId();

    $insItem = $db->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    foreach ($itemLines as $line) {
        $insItem->execute([$orderId, $line['id'], $line['quantity'], $line['unit_price']]);
    }

    $payerEmail = $user['email'] ?? ($guestEmail ?: null);
    $payerName = $user['name'] ?? ($guestName ?: null);
    $pref = mp_create_preference($orderId, $total, $itemLines, $payerEmail, $payerName);

    if (!$pref['ok']) {
        // Payment session couldn't be created — roll back, don't leave a dangling unpaid order.
        $db->rollBack();
        flash('error', t('cart.err_payment_failed', ['error' => $pref['error'] ?? 'unknown error']));
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    $db->prepare("UPDATE orders SET payment_preference_id = ?, payment_provider = 'mercadopago' WHERE id = ?")
        ->execute([$pref['preference_id'], $orderId]);
    $db->commit();

    cart_clear();
    header('Location: ' . $pref['init_point']);
    exit;
}

$cart = cart_get();
$items = [];
$total = 0;
if ($cart) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = db()->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    foreach ($stmt->fetchAll() as $row) {
        $qty = $cart[$row['id']];
        $items[] = ['row' => $row, 'qty' => $qty, 'subtotal' => $row['price'] * $qty];
        $total += $row['price'] * $qty;
    }
}

$pageTitle = t('cart.title') . ' | Franca';
$active = '';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>

<h1 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-primary mb-lg"><?= e(t('cart.title')) ?></h1>

<?php if (!$items): ?>
  <div class="bg-surface-container-lowest rounded-xl p-xl text-center">
    <p class="font-body-lg text-on-surface-variant mb-md"><?= e(t('cart.empty')) ?></p>
    <a href="<?= BASE_URL ?>/menu.php" class="btn-lift bg-primary text-on-primary px-md py-3 rounded-lg font-label-md inline-block"><?= e(t('cart.browse_menu')) ?></a>
  </div>
<?php else: ?>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-lg items-start">
    <div class="lg:col-span-7 space-y-md">
      <?php foreach ($items as $it): ?>
      <div class="flex items-center gap-md bg-surface-container-lowest rounded-xl p-md">
        <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
          <img class="w-full h-full object-cover" src="<?= img_url($it['row']['image']) ?>" alt="<?= e(mi_field($it['row'], 'name')) ?>">
        </div>
        <div class="flex-1">
          <h3 class="font-headline-sm text-headline-sm"><?= e(mi_field($it['row'], 'name')) ?></h3>
          <p class="font-caption text-on-surface-variant"><?= e(t('cart.qty')) ?> <?= (int)$it['qty'] ?> × <?= money($it['row']['price']) ?></p>
        </div>
        <div class="text-right">
          <p class="font-label-md text-secondary mb-2"><?= money($it['subtotal']) ?></p>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="menu_item_id" value="<?= (int)$it['row']['id'] ?>">
            <button class="text-error text-sm underline" type="submit"><?= e(t('cart.remove')) ?></button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="lg:col-span-5">
      <form method="post" class="bg-surface-container-lowest rounded-xl p-lg space-y-4 editorial-shadow">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="checkout">
        <div class="flex justify-between font-headline-sm text-headline-sm text-primary pb-2 border-b border-outline-variant/30">
          <span><?= e(t('cart.total')) ?></span><span><?= money($total) ?></span>
        </div>
        <div>
          <label class="font-label-md text-primary block mb-1"><?= e(t('cart.service_mode')) ?></label>
          <select name="service_mode" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
            <option value="dine-in"><?= e(t('home.dine_in')) ?></option>
            <option value="takeaway"><?= e(t('home.takeaway')) ?></option>
          </select>
        </div>
        <?php if (!current_user()): ?>
        <div>
          <label class="font-label-md text-primary block mb-1"><?= e(t('cart.your_name')) ?></label>
          <input name="guest_name" type="text" required class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md" placeholder="<?= e(t('cart.full_name_placeholder')) ?>">
        </div>
        <div>
          <label class="font-label-md text-primary block mb-1"><?= e(t('cart.phone')) ?></label>
          <input name="guest_phone" type="tel" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md" placeholder="+598 ...">
        </div>
        <div>
          <label class="font-label-md text-primary block mb-1"><?= e(t('cart.email_receipt')) ?></label>
          <input name="guest_email" type="email" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md" placeholder="you@example.com">
        </div>
        <p class="font-caption text-on-surface-variant"><a class="underline text-secondary" href="<?= BASE_URL ?>/login.php"><?= e(t('auth.log_in')) ?></a> <?= e(t('cart.login_prompt')) ?></p>
        <?php endif; ?>

        <?php if (!mp_configured()): ?>
          <div class="bg-error-container text-on-error-container rounded-lg p-3 text-sm">
            <?= e(t('cart.payment_unavailable_notice')) ?> <a class="underline" href="<?= BASE_URL ?>/reservations.php"><?= e(t('cart.reservations_page_link')) ?></a>.
          </div>
          <button class="w-full bg-outline-variant text-on-surface-variant py-4 rounded-lg font-label-md uppercase tracking-widest cursor-not-allowed" type="button" disabled><?= e(t('cart.payment_unavailable_btn')) ?></button>
        <?php else: ?>
          <p class="font-caption text-on-surface-variant"><?= e(t('cart.redirect_notice')) ?></p>
          <button class="btn-lift w-full bg-primary text-on-primary py-4 rounded-lg font-label-md uppercase tracking-widest flex items-center justify-center gap-2" type="submit">
            <span class="material-symbols-outlined">lock</span> <?= e(t('cart.pay_with_mercadopago')) ?>
          </button>
        <?php endif; ?>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
