<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
ensure_logged_in();

$pageTitle = 'My Orders';
$activeNav = 'orders';
$userId = current_user_id();

$sql = "
    SELECT
        o.order_id,
        o.total_amount,
        o.status,
        o.take_option,
        o.preparation_time,
        o.order_time,
        GROUP_CONCAT(CONCAT(oi.quantity, 'x ', m.name) SEPARATOR ', ') AS items_summary
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.order_id
    LEFT JOIN menu_items m ON m.item_id = oi.item_id
    WHERE o.user_id = ?
    GROUP BY o.order_id, o.total_amount, o.status, o.take_option, o.preparation_time, o.order_time
    ORDER BY o.order_time DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$orders = [];
while ($row = $res->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card-header">
  <div>
    <h1 style="margin:0 0 4px 0;">My Orders</h1>
    <p class="muted" style="margin:0;">Track your current and past canteen orders.</p>
  </div>
  <a class="btn btn-primary btn-sm" href="<?php echo e(app_url('public/menu.php')); ?>">+ New Order</a>
</div>

<?php if (empty($orders)): ?>
  <div class="card empty-state">
    <h3 style="margin-top:0;">You haven't placed any orders yet</h3>
    <p>Visit the menu to pick your first meal.</p>
    <a class="btn btn-primary" href="<?php echo e(app_url('public/menu.php')); ?>">Browse Menu</a>
  </div>
<?php else: ?>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Order</th>
            <th>Items</th>
            <th>Option</th>
            <th>Status</th>
            <th>Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td>
                <strong>#<?php echo (int) $o['order_id']; ?></strong><br>
                <small class="muted"><?php echo e($o['order_time']); ?></small>
              </td>
              <td>
                <?php echo e($o['items_summary'] ?: 'Order items'); ?><br>
                <small class="muted">Est. prep: ~<?php echo (int) $o['preparation_time']; ?> mins</small>
              </td>
              <td><span class="badge badge-muted"><?php echo e(ucfirst($o['take_option'])); ?></span></td>
              <td>
                <span class="badge badge-<?php echo e($o['status']); ?>">
                  <?php echo e(order_status_label((string) $o['status'])); ?>
                </span>
              </td>
              <td><strong><?php echo e(format_currency($o['total_amount'])); ?></strong></td>
              <td>
                <a class="btn btn-secondary btn-sm" href="<?php echo e(app_url('public/order.php?id=' . (int) $o['order_id'])); ?>">Details</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
