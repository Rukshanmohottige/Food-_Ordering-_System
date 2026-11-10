<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
ensure_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_valid_post('admin/menu_manage.php');
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create' || $action === 'update') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? 'Mains')) ?: 'Mains';
        $desc = trim((string) ($_POST['description'] ?? ''));
        $price = (float) ($_POST['price'] ?? 0);
        $prep = max(1, min(120, (int) ($_POST['prep_minutes'] ?? 10)));
        $imageUrl = trim((string) ($_POST['image_url'] ?? ''));

        if ($name === '' || $price <= 0) {
            set_flash('error', 'Item name and a price greater than 0 are required.');
            redirect_to('admin/menu_manage.php');
        }

        if ($imageUrl !== '' && !preg_match('#^(https?://|/|assets/|\.\./)#i', $imageUrl)) {
            set_flash('error', 'Image URL must start with http://, https://, /, or assets/.');
            redirect_to('admin/menu_manage.php');
        }

        // Optional uploaded food image file takes precedence over typed URL
        if (!empty($_FILES['image_file']['name']) && ($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $errCode = (int) ($_FILES['image_file']['error'] ?? UPLOAD_ERR_OK);
            if ($errCode !== UPLOAD_ERR_OK) {
                set_flash('error', 'Image upload failed (error code ' . $errCode . ').');
                redirect_to('admin/menu_manage.php');
            }
            $size = (int) ($_FILES['image_file']['size'] ?? 0);
            if ($size <= 0 || $size > 3 * 1024 * 1024) {
                set_flash('error', 'Uploaded image must be under 3 MB.');
                redirect_to('admin/menu_manage.php');
            }
            $tmpPath = (string) ($_FILES['image_file']['tmp_name'] ?? '');
            $info = @getimagesize($tmpPath);
            $mime = is_array($info) ? ($info['mime'] ?? '') : '';
            $extMap = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];
            if (!isset($extMap[$mime])) {
                set_flash('error', 'Only JPG, PNG, WebP, or GIF images are allowed.');
                redirect_to('admin/menu_manage.php');
            }
            $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'menu';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'dish_' . bin2hex(random_bytes(6)) . '.' . $extMap[$mime];
            if (!move_uploaded_file($tmpPath, $uploadDir . DIRECTORY_SEPARATOR . $filename)) {
                set_flash('error', 'Could not save uploaded image.');
                redirect_to('admin/menu_manage.php');
            }
            $imageUrl = 'assets/images/menu/' . $filename;
        }

        $imageParam = $imageUrl !== '' ? $imageUrl : null;

        if ($action === 'create') {
            $stmt = $conn->prepare('INSERT INTO menu_items (name, category, description, price, prep_minutes, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?, 1)');
            $stmt->bind_param('sssdis', $name, $category, $desc, $price, $prep, $imageParam);
            $stmt->execute();
            $stmt->close();
            set_flash('success', "Added \"$name\" to the menu.");
        } else {
            $itemId = (int) ($_POST['item_id'] ?? 0);
            $stmt = $conn->prepare('UPDATE menu_items SET name = ?, category = ?, description = ?, price = ?, prep_minutes = ?, image_url = ? WHERE item_id = ?');
            $stmt->bind_param('sssdisi', $name, $category, $desc, $price, $prep, $imageParam, $itemId);
            $stmt->execute();
            $stmt->close();
            set_flash('success', "Updated \"$name\".");
        }
        redirect_to('admin/menu_manage.php');
    }

    if ($action === 'toggle') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $stmt = $conn->prepare('UPDATE menu_items SET is_available = NOT is_available WHERE item_id = ?');
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $stmt->close();
        set_flash('info', 'Item availability updated.');
        redirect_to('admin/menu_manage.php');
    }

    if ($action === 'delete') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $check = $conn->prepare('SELECT COUNT(*) AS cnt FROM order_items WHERE item_id = ?');
        $check->bind_param('i', $itemId);
        $check->execute();
        $usedCount = (int) ($check->get_result()->fetch_assoc()['cnt'] ?? 0);
        $check->close();

        if ($usedCount > 0) {
            $soft = $conn->prepare('UPDATE menu_items SET is_available = 0 WHERE item_id = ?');
            $soft->bind_param('i', $itemId);
            $soft->execute();
            $soft->close();
            set_flash('warning', 'This dish appears in past orders, so it was marked Sold Out to preserve order history.');
        } else {
            $del = $conn->prepare('DELETE FROM menu_items WHERE item_id = ?');
            $del->bind_param('i', $itemId);
            $del->execute();
            $del->close();
            set_flash('success', 'Menu item deleted.');
        }
        redirect_to('admin/menu_manage.php');
    }
}

$editItem = null;
$editId = (int) ($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmt = $conn->prepare('SELECT * FROM menu_items WHERE item_id = ? LIMIT 1');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editItem = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$items = [];
$res = $conn->query('SELECT * FROM menu_items ORDER BY is_available DESC, category ASC, created_at DESC');
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}

$pageTitle = 'Manage Menu';
$activeAdminNav = 'menu';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="card-header">
  <h1 style="margin:0;">Manage Menu</h1>
  <a class="btn btn-secondary btn-sm" href="<?php echo e(app_url('public/menu.php')); ?>">Preview Storefront Menu &rarr;</a>
</div>

<div class="grid-2" style="grid-template-columns: 1fr 1.6fr; align-items:start;">
  <div class="card">
    <h3 style="margin-top:0;"><?php echo $editItem ? 'Edit Dish #' . (int) $editItem['item_id'] : 'Add New Dish'; ?></h3>
    <form method="POST" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'create'; ?>">
      <?php if ($editItem): ?>
        <input type="hidden" name="item_id" value="<?php echo (int) $editItem['item_id']; ?>">
      <?php endif; ?>

      <div class="form-group">
        <label for="name">Dish Name</label>
        <input id="name" type="text" name="name" value="<?php echo e($editItem['name'] ?? ''); ?>" placeholder="e.g., Chicken Fried Rice" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="category">Category</label>
          <input id="category" type="text" name="category" list="catSuggestions" value="<?php echo e($editItem['category'] ?? 'Rice & Noodles'); ?>" required>
          <datalist id="catSuggestions">
            <option value="Rice & Noodles">
            <option value="Snacks & Burgers">
            <option value="Breakfast">
            <option value="Beverages">
            <option value="Desserts">
            <option value="Mains">
          </datalist>
        </div>
        <div class="form-group">
          <label for="price">Price (Rs.)</label>
          <input id="price" type="number" step="0.01" min="1" name="price" value="<?php echo e($editItem['price'] ?? ''); ?>" placeholder="450.00" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="prep_minutes">Prep Time (mins)</label>
          <input id="prep_minutes" type="number" min="1" max="120" name="prep_minutes" value="<?php echo e((string) ($editItem['prep_minutes'] ?? 10)); ?>" required>
        </div>
        <div class="form-group">
          <label for="image_file">Upload Food Image</label>
          <input id="image_file" type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>
      </div>

      <div class="form-group">
        <label for="image_url">Or Image URL / Path (Optional)</label>
        <input id="image_url" type="text" name="image_url" value="<?php echo e($editItem['image_url'] ?? ''); ?>" placeholder="assets/images/menu/dish.jpg or https://...">
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3" placeholder="Short description of ingredients or portion"><?php echo e($editItem['description'] ?? ''); ?></textarea>
      </div>

      <div style="display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary" style="flex:1;">
          <?php echo $editItem ? 'Save Changes' : 'Add Item'; ?>
        </button>
        <?php if ($editItem): ?>
          <a class="btn btn-secondary" href="<?php echo e(app_url('admin/menu_manage.php')); ?>">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Menu Items (<?php echo count($items); ?>)</h3>
    <?php if (empty($items)): ?>
      <p class="muted">No menu items yet. Add your first dish on the left.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Dish</th>
              <th>Price</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <?php $thumb = media_url($it['image_url'] ?? null); ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:10px;">
                    <?php if ($thumb !== null): ?>
                      <img src="<?php echo e($thumb); ?>" alt="<?php echo e($it['name']); ?>" class="dish-thumb">
                    <?php else: ?>
                      <span class="dish-thumb-fallback" aria-hidden="true"><?php echo category_emoji((string) ($it['category'] ?? '')); ?></span>
                    <?php endif; ?>
                    <div>
                      <strong><?php echo e($it['name']); ?></strong><br>
                      <small class="muted"><?php echo e($it['category'] ?? 'Mains'); ?> &bull; ~<?php echo (int) ($it['prep_minutes'] ?? 10); ?> min</small>
                    </div>
                  </div>
                </td>
                <td><?php echo e(format_currency($it['price'])); ?></td>
                <td>
                  <?php if ((int) $it['is_available'] === 1): ?>
                    <span class="badge badge-completed">Available</span>
                  <?php else: ?>
                    <span class="badge badge-cancelled">Sold Out</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <a class="btn btn-secondary btn-sm" href="<?php echo e(app_url('admin/menu_manage.php?edit=' . (int) $it['item_id'])); ?>">Edit</a>
                    <form method="POST" style="display:inline;">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="action" value="toggle">
                      <input type="hidden" name="item_id" value="<?php echo (int) $it['item_id']; ?>">
                      <button type="submit" class="btn btn-warning btn-sm">
                        <?php echo (int) $it['is_available'] === 1 ? 'Mark Sold Out' : 'Enable'; ?>
                      </button>
                    </form>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this menu item?');">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="item_id" value="<?php echo (int) $it['item_id']; ?>">
                      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
