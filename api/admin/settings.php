<?php
$pageTitle = 'Settings | Franca Admin';
$active = 'settings';
require __DIR__ . '/includes/bootstrap.php';

$db = db();

$fields = [
    'site_name' => 'Site Name',
    'address' => 'Address',
    'phone' => 'Phone',
    'email' => 'Contact Email',
    'hours_mon_fri' => 'Hours (Mon-Fri)',
    'hours_sat' => 'Hours (Saturday)',
    'hours_sun' => 'Hours (Sunday)',
    'instagram' => 'Instagram Handle',
    'map_lat' => 'Map Latitude',
    'map_lng' => 'Map Longitude',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $errors = [];
    foreach (['map_lat', 'map_lng'] as $coordKey) {
        $v = trim($_POST[$coordKey] ?? '');
        if ($v !== '' && !is_numeric($v)) {
            $errors[] = $fields[$coordKey] . ' must be a number.';
        }
    }
    if ($errors) {
        flash('error', implode(' ', $errors));
    } else {
        $stmt = $db->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?");
        foreach (array_keys($fields) as $key) {
            $stmt->execute([trim($_POST[$key] ?? ''), $key]);
        }
        flash('success', 'Settings saved.');
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

<h1 class="font-headline-md text-headline-md text-primary mb-lg">Site Settings</h1>

<form method="post" class="bg-surface-container-lowest rounded-xl p-lg editorial-shadow space-y-4 max-w-xl">
  <?= csrf_field() ?>
  <?php foreach ($fields as $key => $label): ?>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e($label) ?></label>
      <input name="<?= e($key) ?>" type="text" value="<?= e($current[$key] ?? '') ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
  <?php endforeach; ?>
  <button class="bg-primary text-on-primary px-lg py-3 rounded-lg font-label-md" type="submit">Save Settings</button>
</form>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
