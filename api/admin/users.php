<?php
$pageTitle = 'Users | Franca Admin';
$active = 'users';
require __DIR__ . '/includes/bootstrap.php';

$db = db();
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify() && ($_POST['action'] ?? '') === 'toggle_role') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id === (int) $me['id']) {
        flash('error', t('admin.cannot_change_own_role'));
    } else {
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $newRole = $row['role'] === 'admin' ? 'customer' : 'admin';
            $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$newRole, $id]);
            flash('success', t('admin.user_role_updated'));
        }
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

require __DIR__ . '/includes/layout_head.php';
?>

<h1 class="font-headline-md text-headline-md text-primary mb-lg"><?= e(t('admin.users_title')) ?></h1>

<div class="bg-surface-container-lowest rounded-xl overflow-x-auto editorial-shadow">
  <table class="w-full text-left min-w-[600px]">
    <thead class="bg-surface-container-high">
      <tr>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_name')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_email')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_role')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_joined')) ?></th>
        <th class="p-3 font-label-md text-sm text-right"><?= e(t('admin.col_actions')) ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr class="border-t border-outline-variant/20">
        <td class="p-3 font-body-md"><?= e($u['name']) ?></td>
        <td class="p-3 font-body-md"><?= e($u['email']) ?></td>
        <td class="p-3"><span class="px-2 py-0.5 rounded-full text-[11px] font-label-md uppercase <?= $u['role'] === 'admin' ? 'bg-accent/20 text-accent-dark' : 'bg-surface-container-high text-on-surface-variant' ?>"><?= e($u['role']) ?></span></td>
        <td class="p-3 font-caption text-on-surface-variant"><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
        <td class="p-3 text-right">
          <?php if ((int)$u['id'] !== (int)$me['id']): ?>
          <form method="post" onsubmit="return confirm(<?= json_encode(t('admin.confirm_role_change')) ?>);">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle_role">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="text-secondary font-label-md text-sm underline" type="submit"><?= $u['role'] === 'admin' ? e(t('admin.demote_to_customer')) : e(t('admin.promote_to_admin')) ?></button>
          </form>
          <?php else: ?>
            <span class="font-caption text-on-surface-variant"><?= e(t('admin.you')) ?></span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
