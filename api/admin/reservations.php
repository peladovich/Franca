<?php
$pageTitle = 'Reservations | Franca Admin';
$active = 'reservations';
require __DIR__ . '/includes/bootstrap.php';

$db = db();
$validStatuses = ['pending', 'confirmed', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify() && ($_POST['action'] ?? '') === 'update_status') {
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, $validStatuses, true)) {
        $db->prepare("UPDATE reservations SET status = ? WHERE id = ?")->execute([$status, $id]);
        flash('success', t('admin.reservation_updated'));
    }
    header('Location: ' . BASE_URL . '/admin/reservations.php');
    exit;
}

$reservations = $db->query("SELECT * FROM reservations ORDER BY reservation_date DESC, reservation_time DESC")->fetchAll();

$statusColors = [
    'pending' => 'bg-secondary-container text-on-secondary-container',
    'confirmed' => 'bg-secondary text-on-secondary',
    'cancelled' => 'bg-error-container text-on-error-container',
];

require __DIR__ . '/includes/layout_head.php';
?>

<h1 class="font-headline-md text-headline-md text-primary mb-lg"><?= e(t('admin.reservations_title')) ?></h1>

<div class="bg-surface-container-lowest rounded-xl overflow-x-auto editorial-shadow">
  <table class="w-full text-left min-w-[720px]">
    <thead class="bg-surface-container-high">
      <tr>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_guest')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_datetime')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_party')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_requests')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_status')) ?></th>
        <th class="p-3 font-label-md text-sm text-right"><?= e(t('admin.col_actions')) ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($reservations as $r): ?>
      <tr class="border-t border-outline-variant/20">
        <td class="p-3 font-body-md"><?= e($r['name']) ?><br><span class="font-caption text-on-surface-variant"><?= e($r['email']) ?><?= $r['phone'] ? ' · ' . e($r['phone']) : '' ?></span></td>
        <td class="p-3 font-body-md"><?= e(date('M j, Y', strtotime($r['reservation_date']))) ?><br><span class="font-caption text-on-surface-variant"><?= e(date('g:i A', strtotime($r['reservation_time']))) ?></span></td>
        <td class="p-3 font-body-md"><?= (int)$r['party_size'] ?></td>
        <td class="p-3 font-body-md text-sm max-w-xs"><?= e($r['special_requests']) ?></td>
        <td class="p-3"><span class="px-2 py-0.5 rounded-full text-[11px] font-label-md uppercase <?= $statusColors[$r['status']] ?? '' ?>"><?= e(status_label($r['status'])) ?></span></td>
        <td class="p-3 text-right">
          <form method="post" class="flex gap-2 justify-end">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <select name="status" class="bg-background border border-outline-variant/50 rounded-lg px-2 py-1 font-body-md text-sm">
              <?php foreach ($validStatuses as $s): ?>
                <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="bg-accent text-on-accent px-3 py-1 rounded-full font-label-md text-sm" type="submit"><?= e(t('admin.save')) ?></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$reservations): ?><tr><td colspan="6" class="p-3 font-body-md text-on-surface-variant"><?= e(t('admin.no_reservations_yet')) ?></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
