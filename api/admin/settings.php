<?php
$pageTitle = 'Settings | Franca Admin';
$active = 'settings';
require __DIR__ . '/includes/bootstrap.php';

$db = db();

$fields = [
    'site_name' => t('admin.field_site_name'),
    'address' => t('admin.field_address'),
    'phone' => t('admin.field_phone'),
    'email' => t('admin.field_email'),
    'hours_mon_fri' => t('admin.field_hours_mon_fri'),
    'hours_sat' => t('admin.field_hours_sat'),
    'hours_sun' => t('admin.field_hours_sun'),
    'instagram' => t('admin.field_instagram'),
    'map_lat' => t('admin.field_map_lat'),
    'map_lng' => t('admin.field_map_lng'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $errors = [];
    foreach (['map_lat', 'map_lng'] as $coordKey) {
        $v = trim($_POST[$coordKey] ?? '');
        if ($v !== '' && !is_numeric($v)) {
            $errors[] = $fields[$coordKey] . ' ' . t('admin.err_must_be_number');
        }
    }
    if ($errors) {
        flash('error', implode(' ', $errors));
    } else {
        $stmt = $db->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?");
        foreach (array_keys($fields) as $key) {
            $stmt->execute([trim($_POST[$key] ?? ''), $key]);
        }
        flash('success', t('admin.settings_saved'));
    }
    header('Location: ' . BASE_URL . '/admin/settings.php');
    exit;
}

$current = [];
foreach ($db->query("SELECT `key`, `value` FROM settings") as $row) {
    $current[$row['key']] = $row['value'];
}

require __DIR__ . '/includes/layout_head.php';
?>

<h1 class="font-headline-md text-headline-md text-primary mb-lg"><?= e(t('admin.settings_title')) ?></h1>

<form method="post" class="bg-surface-container-lowest rounded-xl p-lg editorial-shadow space-y-4 max-w-xl">
  <?= csrf_field() ?>
  <?php foreach ($fields as $key => $label): ?>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e($label) ?></label>
      <input name="<?= e($key) ?>" type="text" value="<?= e($current[$key] ?? '') ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
  <?php endforeach; ?>
  <button class="bg-accent text-on-accent px-lg py-3 rounded-full font-label-md" type="submit"><?= e(t('admin.save_settings')) ?></button>
</form>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
