<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
ensure_admin();

$validStatuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];
$filterStatus = (string) ($_GET['status'] ?? 'all');
if ($filterStatus !== 'all' && !in_array($filterStatus, $validStatuses, true)) {
    $filterStatus = 'all';
}

$sql = "
    SELECT
        o.order_id,
        o.user_id,
        o.total_amount,
        o.status,
        o.take_option,
        o.preparation_time,
        o.notes,
        o.order_time,
        u.name AS customer_name,
        u.email AS customer_email,
        GROUP_CONCAT(CONCAT(oi.quantity, 'x ', m.name) SEPARATOR ', ') AS items_summary
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN order_items oi ON oi.order_id = o.order_id
    LEFT JOIN menu_items m ON m.item_id = oi.item_id
";

if ($filterStatus !== 'all') {
    $sql .= ' WHERE o.status = ? ';
}

$sql .= ' GROUP BY o.order_id, o.user_id, o.total_amount, o.status, o.take_option, o.preparation_time, o.notes, o.order_time, u.name, u.email ORDER BY o.order_time DESC';

$stmt = $conn->prepare($sql);
if ($filterStatus !== 'all') {
    $stmt->bind_param('s', $filterStatus);
}
$stmt->execute();
$res = $stmt->get_result();
$orders = [];
while ($row = $res->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

$pageTitle = 'Manage Orders';
$activeAdminNav = 'orders';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="card-header">
  <h1 style="margin:0;">Manage Orders</h1>
  <div class="filter-tabs">
    <a class="filter-tab <?php echo $filterStatus === 'all' ? 'active' : ''; ?>" href="<?php echo e(app_url('admin/orders_manage.php')); ?>">All</a>
    <?php foreach ($validStatuses as $st): ?>
      <a class="filter-tab <?php echo $filterStatus === $st ? 'active' : ''; ?>" href="<?php echo e(app_url('admin/orders_manage.php?status=' . $st)); ?>">
        <?php echo e(order_status_label($st)); ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <h3 style="margin-top:0;">No orders in this filter</h3>
      <p>Orders placed by customers will appear here immediately.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Items &amp; Notes</th>
            <th>Status</th>
            <th>Total</th>
            <th>Update Status</th>
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
                <strong><?php echo e($o['customer_name']); ?></strong><br>
                <small class="muted"><?php echo e($o['customer_email']); ?></small>
              </td>
              <td>
                <div><?php echo e($o['items_summary'] ?: '—'); ?></div>
                <small class="muted">
                  <?php echo e(ucfirst((string) $o['take_option'])); ?> &bull; ~<?php echo (int) $o['preparation_time']; ?> min
                </small>
                <?php if (!empty($o['notes'])): ?>
                  <br><small style="color:var(--primary-dark);">Note: <?php echo e($o['notes']); ?></small>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge badge-<?php echo e($o['status']); ?>">
                  <?php echo e(order_status_label((string) $o['status'])); ?>
                </span>
              </td>
              <td><strong><?php echo e(format_currency($o['total_amount'])); ?></strong></td>
              <td>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                  <?php
                  $buttons = [
                      'preparing' => ['Preparing', 'btn-warning'],
                      'ready' => ['Ready', 'btn-info'],
                      'completed' => ['Complete', 'btn-success'],
                      'cancelled' => ['Cancel', 'btn-danger'],
                  ];
                  foreach ($buttons as $targetStatus => [$label, $btnCls]):
                      if ($o['status'] === $targetStatus) {
                          continue;
                      }
                  ?>
                    <form action="<?php echo e(app_url('actions/update_order_status.php')); ?>" method="POST" style="display:inline;">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="order_id" value="<?php echo (int) $o['order_id']; ?>">
                      <input type="hidden" name="status" value="<?php echo e($targetStatus); ?>">
                      <input type="hidden" name="filter_status" value="<?php echo e($filterStatus); ?>">
                      <button type="submit" class="btn <?php echo $btnCls; ?> btn-sm"><?php echo e($label); ?></button>
                    </form>
                  <?php endforeach; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
