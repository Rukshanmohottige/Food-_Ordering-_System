<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
ensure_logged_in();

$pageTitle = 'Cart & Checkout';
$activeNav = 'cart';

$cart = cart_items();
$cartRows = [];
$grandTotal = 0.0;
$estPrep = 0;

if (!empty($cart)) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("SELECT item_id, name, category, price, prep_minutes, is_available FROM menu_items WHERE item_id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
    $dbItems = [];
    while ($row = $res->fetch_assoc()) {
        $dbItems[(int) $row['item_id']] = $row;
    }
    $stmt->close();

    foreach ($cart as $itemId => $qty) {
        if (!isset($dbItems[$itemId])) {
            cart_set($itemId, 0);
            continue;
        }
        $item = $dbItems[$itemId];
        $lineTotal = ((float) $item['price']) * $qty;
        if ((int) $item['is_available'] === 1) {
            $grandTotal += $lineTotal;
            $itemPrep = max(1, (int) ($item['prep_minutes'] ?? 10));
            $estPrep = max($estPrep, $itemPrep + ($qty > 3 ? 5 : 0));
        }
        $cartRows[] = [
            'item_id' => $itemId,
            'name' => $item['name'],
            'category' => $item['category'] ?? 'Mains',
            'price' => (float) $item['price'],
            'qty' => $qty,
            'line_total' => $lineTotal,
            'is_available' => (int) $item['is_available'] === 1,
        ];
    }
}

$hasUnavailable = false;
foreach ($cartRows as $r) {
    if (!$r['is_available']) {
        $hasUnavailable = true;
        break;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card-header">
  <h1 style="margin:0;">Your Cart</h1>
  <a class="btn btn-secondary btn-sm" href="<?php echo e(app_url('public/menu.php')); ?>">&larr; Continue Browsing</a>
</div>

<?php if (empty($cartRows)): ?>
  <div class="card empty-state">
    <h3 style="margin-top:0;">Your cart is empty</h3>
    <p>Add delicious meals from the canteen menu to place an order.</p>
    <a class="btn btn-primary" href="<?php echo e(app_url('public/menu.php')); ?>">Browse Menu</a>
  </div>
<?php else: ?>
  <div class="grid-2" style="grid-template-columns: 1.5fr 1fr; align-items:start;">
    <div class="card">
      <div class="card-header">
        <h3>Selected Items</h3>
        <form action="<?php echo e(app_url('actions/cart.php')); ?>" method="POST" onsubmit="return confirm('Clear all items from your cart?');">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="clear">
          <button type="submit" class="btn btn-secondary btn-sm">Clear Cart</button>
        </form>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th>Unit Price</th>
              <th>Qty</th>
              <th>Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cartRows as $row): ?>
              <tr>
                <td>
                  <strong><?php echo e($row['name']); ?></strong><br>
                  <small class="muted"><?php echo e($row['category']); ?></small>
                  <?php if (!$row['is_available']): ?>
                    <br><span class="badge badge-cancelled">Currently Sold Out — remove to checkout</span>
                  <?php endif; ?>
                </td>
                <td><?php echo e(format_currency($row['price'])); ?></td>
                <td>
                  <form action="<?php echo e(app_url('actions/cart.php')); ?>" method="POST" class="qty-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="item_id" value="<?php echo (int) $row['item_id']; ?>">
                    <input type="number" name="quantity" value="<?php echo (int) $row['qty']; ?>" min="1" max="50" style="width:68px;padding:6px;">
                    <button type="submit" class="btn btn-secondary btn-sm">Update</button>
                  </form>
                </td>
                <td><strong><?php echo e(format_currency($row['line_total'])); ?></strong></td>
                <td>
                  <form action="<?php echo e(app_url('actions/cart.php')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="item_id" value="<?php echo (int) $row['item_id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm" aria-label="Remove item">&times;</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <h3 style="margin-top:0;">Checkout Details</h3>
      <p class="muted" style="margin-top:0;">Estimated preparation time: <strong>~<?php echo max(5, $estPrep); ?> mins</strong></p>

      <div style="display:flex;justify-content:space-between;padding:12px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);margin-bottom:16px;font-size:1.15rem;">
        <span>Total Amount</span>
        <strong style="color:var(--primary-dark);"><?php echo e(format_currency($grandTotal)); ?></strong>
      </div>

      <?php if ($hasUnavailable): ?>
        <div class="alert alert-warning">
          <span>Please remove sold-out items before placing your order.</span>
        </div>
      <?php else: ?>
        <form action="<?php echo e(app_url('actions/place_order.php')); ?>" method="POST">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="mode" value="cart">

          <div class="form-group">
            <label for="take_option">Dining Option</label>
            <select id="take_option" name="take_option" required>
              <option value="takeaway">Takeaway (Counter Pickup)</option>
              <option value="dine-in">Dine-in</option>
            </select>
          </div>

          <div class="form-group">
            <label for="notes">Kitchen Notes (Optional)</label>
            <textarea id="notes" name="notes" rows="3" maxlength="255" placeholder="e.g., Less spicy, pickup at 12:30 PM"></textarea>
          </div>

          <button type="submit" class="btn btn-success" style="width:100%;">Place Order (<?php echo e(format_currency($grandTotal)); ?>)</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
