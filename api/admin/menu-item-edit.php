<?php
$pageTitle = 'Edit Menu Item | Franca Admin';
$active = 'menu-items';
require __DIR__ . '/includes/bootstrap.php';

$db = db();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$item = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        flash('error', t('admin.item_not_found'));
        header('Location: ' . BASE_URL . '/admin/menu-items.php');
        exit;
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = t('admin.err_session_expired');
    } else {
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $badge = trim($_POST['badge'] ?? '');
        $ingredients = trim($_POST['ingredients'] ?? '');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $image = trim($_POST['image'] ?? '') ?: ($item['image'] ?? null);

        if ($name === '') $errors[] = t('admin.err_name_required');
        if ($categoryId <= 0) $errors[] = t('admin.err_category_required');
        if ($price < 0) $errors[] = t('admin.err_price_negative');

        if (!empty($_FILES['image_upload']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image_upload']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $errors[] = t('admin.err_image_type');
            } elseif ($_FILES['image_upload']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = t('admin.err_image_upload_failed');
            } else {
                $newName = bin2hex(random_bytes(8)) . '.' . $ext;
                $dest = __DIR__ . '/../../assets/img/' . $newName;
                if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $dest)) {
                    $image = $newName;
                } else {
                    $errors[] = t('admin.err_image_save_failed');
                }
            }
        }

        if (!$errors) {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
            if ($item) {
                $stmt = $db->prepare("UPDATE menu_items SET category_id=?, name=?, slug=?, description=?, price=?, image=?, badge=?, ingredients=?, is_featured=?, is_available=?, sort_order=? WHERE id=?");
                $stmt->execute([$categoryId, $name, $slug, $description, $price, $image, $badge ?: null, $ingredients ?: null, $isFeatured, $isAvailable, $sortOrder, $item['id']]);
                flash('success', t('admin.item_updated'));
            } else {
                $stmt = $db->prepare("INSERT INTO menu_items (category_id, name, slug, description, price, image, badge, ingredients, is_featured, is_available, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$categoryId, $name, $slug, $description, $price, $image, $badge ?: null, $ingredients ?: null, $isFeatured, $isAvailable, $sortOrder]);
                flash('success', t('admin.item_created'));
            }
            header('Location: ' . BASE_URL . '/admin/menu-items.php');
            exit;
        }
    }
}

$categories = $db->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$existingImages = array_values(array_filter(scandir(__DIR__ . '/../../assets/img'), function ($f) {
    return preg_match('/\.(jpg|jpeg|png|webp)$/i', $f);
}));
sort($existingImages);

$val = fn($key, $default = '') => e((string) ($_POST[$key] ?? $item[$key] ?? $default));

require __DIR__ . '/includes/layout_head.php';
?>

<h1 class="font-headline-md text-headline-md text-primary mb-lg"><?= $item ? e(t('admin.edit_menu_item')) : e(t('admin.add_menu_item')) ?></h1>

<?php if ($errors): ?>
  <div class="bg-error-container text-on-error-container rounded-lg p-3 mb-md text-sm">
    <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="bg-surface-container-lowest rounded-xl p-lg editorial-shadow space-y-4 max-w-2xl">
  <?= csrf_field() ?>
  <?php if ($item): ?><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><?php endif; ?>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('admin.field_name')) ?></label>
      <input name="name" type="text" required value="<?= $val('name') ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('admin.field_category')) ?></label>
      <select name="category_id" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
        <?php foreach ($categories as $cat): ?>
          <option value="<?= (int)$cat['id'] ?>" <?= (int)($item['category_id'] ?? $_POST['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div>
    <label class="font-label-md text-primary block mb-1"><?= e(t('admin.field_description')) ?></label>
    <textarea name="description" rows="3" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md"><?= $val('description') ?></textarea>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('admin.field_price')) ?></label>
      <input name="price" type="number" step="0.01" min="0" value="<?= $val('price', '0.00') ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('admin.field_badge')) ?></label>
      <input name="badge" type="text" value="<?= $val('badge') ?>" placeholder="Most Loved" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('admin.field_sort_order')) ?></label>
      <input name="sort_order" type="number" value="<?= $val('sort_order', '0') ?>" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
  </div>

  <div>
    <label class="font-label-md text-primary block mb-1"><?= e(t('admin.field_ingredients')) ?></label>
    <input name="ingredients" type="text" value="<?= $val('ingredients') ?>" placeholder="Sourdough, Avocado, Sea Salt" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('admin.field_existing_image')) ?></label>
      <select name="image" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
        <option value=""><?= e(t('admin.field_none')) ?></option>
        <?php foreach ($existingImages as $img): ?>
          <option value="<?= e($img) ?>" <?= ($item['image'] ?? '') === $img ? 'selected' : '' ?>><?= e($img) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="font-label-md text-primary block mb-1"><?= e(t('admin.field_upload_new_image')) ?></label>
      <input name="image_upload" type="file" accept="image/*" class="w-full bg-background border border-outline-variant/50 rounded-lg px-4 py-3 font-body-md">
    </div>
  </div>
  <?php if (!empty($item['image'])): ?>
    <img src="<?= img_url($item['image']) ?>" class="w-24 h-24 object-cover rounded-lg" alt="<?= e(t('admin.current_image')) ?>">
  <?php endif; ?>

  <div class="flex gap-6">
    <label class="flex items-center gap-2 font-label-md text-primary">
      <input type="checkbox" name="is_featured" <?= !empty($item['is_featured']) || (isset($_POST['is_featured'])) ? 'checked' : '' ?>> <?= e(t('admin.featured_on_homepage')) ?>
    </label>
    <label class="flex items-center gap-2 font-label-md text-primary">
      <input type="checkbox" name="is_available" <?= ($item === null || !empty($item['is_available'])) || isset($_POST['is_available']) ? 'checked' : '' ?>> <?= e(t('admin.available')) ?>
    </label>
  </div>

  <div class="flex flex-col sm:flex-row gap-3">
    <button class="w-full sm:w-auto text-center bg-accent text-on-accent px-lg py-3 rounded-full font-label-md" type="submit"><?= $item ? e(t('admin.save_changes')) : e(t('admin.create_item')) ?></button>
    <a href="<?= BASE_URL ?>/admin/menu-items.php" class="w-full sm:w-auto text-center px-lg py-3 rounded-full font-label-md text-on-surface-variant border border-outline-variant/50"><?= e(t('admin.cancel')) ?></a>
  </div>
</form>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
