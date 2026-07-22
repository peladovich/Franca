<?php
$pageTitle = 'Menu Items | Franca Admin';
$active = 'menu-items';
require __DIR__ . '/includes/bootstrap.php';

$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify() && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $db->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$id]);
    flash('success', t('admin.item_deleted'));
    header('Location: ' . BASE_URL . '/admin/menu-items.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify() && ($_POST['action'] ?? '') === 'toggle_available') {
    $id = (int) ($_POST['id'] ?? 0);
    $db->prepare("UPDATE menu_items SET is_available = 1 - is_available WHERE id = ?")->execute([$id]);
    header('Location: ' . BASE_URL . '/admin/menu-items.php');
    exit;
}

$items = $db->query("SELECT mi.*, c.name AS category_name FROM menu_items mi JOIN categories c ON c.id = mi.category_id ORDER BY c.sort_order, mi.sort_order")->fetchAll();

require __DIR__ . '/includes/layout_head.php';
?>

<div class="flex justify-between items-center mb-lg">
  <h1 class="font-headline-md text-headline-md text-primary"><?= e(t('admin.menu_items_title')) ?></h1>
  <a href="<?= BASE_URL ?>/admin/menu-item-edit.php" class="bg-accent text-on-accent px-md py-2 rounded-full font-label-md"><?= e(t('admin.add_item')) ?></a>
</div>

<div class="bg-surface-container-lowest rounded-xl overflow-x-auto editorial-shadow">
  <table class="w-full text-left min-w-[720px]">
    <thead class="bg-surface-container-high">
      <tr>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_image')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_name')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_category')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_price')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_featured')) ?></th>
        <th class="p-3 font-label-md text-sm"><?= e(t('admin.col_available')) ?></th>
        <th class="p-3 font-label-md text-sm text-right"><?= e(t('admin.col_actions')) ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
      <tr class="border-t border-outline-variant/20">
        <td class="p-3"><img src="<?= item_photo_url($item) ?>" class="w-14 h-14 object-cover rounded-lg" alt=""></td>
        <td class="p-3 font-body-md"><?= e($item['name']) ?><?= $item['badge'] ? '<br><span class="font-caption text-secondary">' . e($item['badge']) . '</span>' : '' ?></td>
        <td class="p-3 font-body-md"><?= e($item['category_name']) ?></td>
        <td class="p-3 font-body-md"><?= money($item['price']) ?></td>
        <td class="p-3"><?= $item['is_featured'] ? '<span class="material-symbols-outlined text-accent">star</span>' : '' ?></td>
        <td class="p-3">
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle_available">
            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
            <button class="px-2 py-0.5 rounded-full text-[11px] font-label-md uppercase <?= $item['is_available'] ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-on-error-container' ?>" type="submit">
              <?= $item['is_available'] ? e(t('admin.available')) : e(t('admin.hidden')) ?>
            </button>
          </form>
        </td>
        <td class="p-3 text-right whitespace-nowrap">
          <a href="<?= BASE_URL ?>/admin/menu-item-edit.php?id=<?= (int)$item['id'] ?>" class="text-secondary font-label-md text-sm underline mr-3"><?= e(t('admin.edit')) ?></a>
          <form method="post" class="inline" onsubmit="return confirm(<?= json_encode(t('admin.confirm_delete_item')) ?>);">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
            <button class="text-error font-label-md text-sm underline" type="submit"><?= e(t('admin.delete')) ?></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
