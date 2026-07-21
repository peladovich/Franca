<?php
$pageTitle = 'Categories | Franca Admin';
$active = 'categories';
require __DIR__ . '/includes/bootstrap.php';

$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $sort = (int) ($_POST['sort_order'] ?? 0);
        if ($name !== '') {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $stmt = $db->prepare("INSERT INTO categories (name, slug, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$name, trim($slug, '-'), $sort]);
            flash('success', 'Category added.');
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $sort = (int) ($_POST['sort_order'] ?? 0);
        if ($id && $name !== '') {
            $stmt = $db->prepare("UPDATE categories SET name = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $sort, $id]);
            flash('success', 'Category updated.');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $db->prepare("SELECT COUNT(*) FROM menu_items WHERE category_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            flash('error', 'Cannot delete a category that still has menu items.');
        } else {
            $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            flash('success', 'Category deleted.');
        }
    }
    header('Location: ' . BASE_URL . '/admin/categories.php');
    exit;
}

$categories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM menu_items mi WHERE mi.category_id = c.id) AS item_count FROM categories c ORDER BY sort_order")->fetchAll();

require __DIR__ . '/includes/layout_head.php';
?>

<div class="flex justify-between items-center mb-lg">
  <h1 class="font-headline-md text-headline-md text-primary">Categories</h1>
</div>

<div class="bg-surface-container-lowest rounded-xl p-md mb-lg editorial-shadow">
  <h2 class="font-label-md text-primary mb-3">Add Category</h2>
  <form method="post" class="flex flex-col md:flex-row gap-3">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <input name="name" type="text" required placeholder="Category name" class="flex-1 bg-background border border-outline-variant/50 rounded-lg px-4 py-2 font-body-md">
    <input name="sort_order" type="number" value="0" class="w-full md:w-32 bg-background border border-outline-variant/50 rounded-lg px-4 py-2 font-body-md" placeholder="Sort order">
    <button class="bg-primary text-on-primary px-6 py-2 rounded-lg font-label-md" type="submit">Add</button>
  </form>
</div>

<div class="bg-surface-container-lowest rounded-xl overflow-hidden editorial-shadow">
  <table class="w-full text-left">
    <thead class="bg-surface-container-high">
      <tr>
        <th class="p-3 font-label-md text-sm">Name</th>
        <th class="p-3 font-label-md text-sm">Sort</th>
        <th class="p-3 font-label-md text-sm">Items</th>
        <th class="p-3 font-label-md text-sm text-right">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($categories as $cat): ?>
      <tr class="border-t border-outline-variant/20">
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
          <td class="p-3"><input name="name" value="<?= e($cat['name']) ?>" class="bg-transparent font-body-md w-full"></td>
          <td class="p-3"><input name="sort_order" type="number" value="<?= (int)$cat['sort_order'] ?>" class="bg-transparent font-body-md w-16"></td>
          <td class="p-3 font-body-md"><?= (int)$cat['item_count'] ?></td>
          <td class="p-3 text-right whitespace-nowrap">
            <button class="text-secondary font-label-md text-sm underline mr-3" type="submit">Save</button>
        </form>
        <form method="post" class="inline" onsubmit="return confirm('Delete this category?');">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
          <button class="text-error font-label-md text-sm underline" type="submit">Delete</button>
        </form>
          </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
