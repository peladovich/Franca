<?php
$pageTitle = 'Menu Items | Franca Admin';
$active = 'menu-items';
require __DIR__ . '/includes/bootstrap.php';

$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify() && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $db->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$id]);
    flash('success', 'Menu item deleted.');
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
  <h1 class="font-headline-md text-headline-md text-primary">Menu Items</h1>
  <a href="<?= BASE_URL ?>/admin/menu-item-edit.php" class="bg-primary text-on-primary px-md py-2 rounded-lg font-label-md">+ Add Item</a>
</div>

<div class="bg-surface-container-lowest rounded-xl overflow-x-auto editorial-shadow">
  <table class="w-full text-left min-w-[720px]">
    <thead class="bg-surface-container-high">
      <tr>
        <th class="p-3 font-label-md text-sm">Image</th>
        <th class="p-3 font-label-md text-sm">Name</th>
        <th class="p-3 font-label-md text-sm">Category</th>
        <th class="p-3 font-label-md text-sm">Price</th>
        <th class="p-3 font-label-md text-sm">Featured</th>
        <th class="p-3 font-label-md text-sm">Available</th>
        <th class="p-3 font-label-md text-sm text-right">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
      <tr class="border-t border-outline-variant/20">
        <td class="p-3"><img src="<?= img_url($item['image']) ?>" class="w-14 h-14 object-cover rounded-lg" alt=""></td>
        <td class="p-3 font-body-md"><?= e($item['name']) ?><?= $item['badge'] ? '<br><span class="font-caption text-secondary">' . e($item['badge']) . '</span>' : '' ?></td>
        <td class="p-3 font-body-md"><?= e($item['category_name']) ?></td>
        <td class="p-3 font-body-md"><?= money($item['price']) ?></td>
        <td class="p-3"><?= $item['is_featured'] ? '<span class="material-symbols-outlined text-secondary">star</span>' : '' ?></td>
        <td class="p-3">
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle_available">
            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
            <button class="px-2 py-0.5 rounded-full text-[11px] font-label-md uppercase <?= $item['is_available'] ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-on-error-container' ?>" type="submit">
              <?= $item['is_available'] ? 'Available' : 'Hidden' ?>
            </button>
          </form>
        </td>
        <td class="p-3 text-right whitespace-nowrap">
          <a href="<?= BASE_URL ?>/admin/menu-item-edit.php?id=<?= (int)$item['id'] ?>" class="text-secondary font-label-md text-sm underline mr-3">Edit</a>
          <form method="post" class="inline" onsubmit="return confirm('Delete this menu item?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
            <button class="text-error font-label-md text-sm underline" type="submit">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
