<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
ensure_logged_in();

$pageTitle = 'Order Details';
$activeNav = 'orders';

$orderId = (int) ($_GET['id'] ?? 0);
if ($orderId <= 0) {
    set_flash('warning', 'No order selected.');
    redirect_to('public/my_orders.php');
}

$stmt = $conn->prepare('SELECT o.*, u.name AS customer_name, u.email AS customer_email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ? LIMIT 1');
$stmt->bind_param('i', $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    set_flash('error', 'Order not found.');
    redirect_to('public/my_orders.php');
}

// Ownership / role check so students cannot view other users' orders
if ((int) $order['user_id'] !== current_user_id() && !is_admin_or_staff()) {
    http_response_code(403);
    set_flash('error', 'You do not have permission to view that order.');
    redirect_to('public/my_orders.php');
}

$itemStmt = $conn->prepare('SELECT oi.quantity, oi.price, m.name, m.category FROM order_items oi JOIN menu_items m ON oi.item_id = m.item_id WHERE oi.order_id = ?');
$itemStmt->bind_param('i', $orderId);
$itemStmt->execute();
$itemRes = $itemStmt->get_result();
$items = [];
while ($row = $itemRes->fetch_assoc()) {
    $items[] = $row;
}
$itemStmt->close();

$steps = ['pending', 'preparing', 'ready', 'completed'];
$currentStatus = (string) $order['status'];
$currentStepIndex = array_search($currentStatus, $steps, true);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card-header">
  <div>
    <h1 style="margin:0 0 4px 0;">Order #<?php echo (int) $order['order_id']; ?></h1>
    <p class="muted" style="margin:0;">Placed on <?php echo e($order['order_time']); ?></p>
  </div>
  <a class="btn btn-secondary btn-sm" href="<?php echo e(app_url('public/my_orders.php')); ?>">&larr; Back to My Orders</a>
</div>

<div class="card">
  <div class="card-header">
    <div>
      <span class="badge badge-<?php echo e($currentStatus); ?>"><?php echo e(order_status_label($currentStatus)); ?></span>
      <span class="badge badge-muted" style="margin-left:6px;"><?php echo e(ucfirst((string) $order['take_option'])); ?></span>
    </div>
    <span class="muted">Estimated prep time: <strong>~<?php echo (int) $order['preparation_time']; ?> mins</strong></span>
  </div>

  <?php if ($currentStatus === 'cancelled'): ?>
    <div class="alert alert-error" style="margin-bottom:0;">
      <span>This order has been cancelled.</span>
    </div>
  <?php else: ?>
    <div class="stepper">
      <?php foreach ($steps as $idx => $stepKey): ?>
        <?php
        $cls = 'step';
        if ($currentStepIndex !== false) {
            if ($idx < $currentStepIndex || ($currentStatus === 'completed' && $idx === $currentStepIndex)) {
                $cls .= ' done';
            } elseif ($idx === $currentStepIndex) {
                $cls .= ' current';
            }
        }
        ?>
        <div class="<?php echo $cls; ?>">
          <?php echo ($idx + 1) . '. ' . e(order_status_label($stepKey)); ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<div class="grid-2" style="grid-template-columns: 1.5fr 1fr; align-items:start;">
  <div class="card">
    <h3 style="margin-top:0;">Ordered Items</h3>
    <?php if (empty($items)): ?>
      <p class="muted">No item rows recorded for this order.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Dish</th>
              <th>Unit Price</th>
              <th>Qty</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td>
                  <strong><?php echo e($it['name']); ?></strong><br>
                  <small class="muted"><?php echo e($it['category'] ?? 'Mains'); ?></small>
                </td>
                <td><?php echo e(format_currency($it['price'])); ?></td>
                <td><?php echo (int) $it['quantity']; ?></td>
                <td><strong><?php echo e(format_currency(((float) $it['price']) * (int) $it['quantity'])); ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Summary</h3>
    <p><strong>Customer:</strong> <?php echo e($order['customer_name']); ?></p>
    <p><strong>Option:</strong> <?php echo e(ucfirst((string) $order['take_option'])); ?></p>
    <?php if (!empty($order['notes'])): ?>
      <p><strong>Kitchen Notes:</strong> <?php echo e($order['notes']); ?></p>
    <?php endif; ?>
    <hr style="border:none;border-top:1px solid var(--border);margin:14px 0;">
    <div style="display:flex;justify-content:space-between;font-size:1.15rem;">
      <span>Total</span>
      <strong style="color:var(--primary-dark);"><?php echo e(format_currency($order['total_amount'])); ?></strong>
    </div>

    <?php if ($currentStatus === 'pending' && (int) $order['user_id'] === current_user_id()): ?>
      <form action="<?php echo e(app_url('actions/cancel_order.php')); ?>" method="POST" style="margin-top:18px;" onsubmit="return confirm('Are you sure you want to cancel this order?');">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="order_id" value="<?php echo (int) $order['order_id']; ?>">
        <button type="submit" class="btn btn-danger" style="width:100%;">Cancel Pending Order</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
