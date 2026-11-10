<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
ensure_admin();

$pageTitle = 'Dashboard';
$activeAdminNav = 'dashboard';

$statsRow = $conn->query("
    SELECT
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_cnt,
        SUM(CASE WHEN status IN ('preparing', 'ready') THEN 1 ELSE 0 END) AS active_cnt,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_cnt,
        COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END), 0) AS total_income,
        COALESCE(SUM(CASE WHEN status = 'completed' AND DATE(order_time) = CURDATE() THEN total_amount ELSE 0 END), 0) AS today_income
    FROM orders
")->fetch_assoc();

$menuAvailable = (int) ($conn->query('SELECT COUNT(*) AS cnt FROM menu_items WHERE is_available = 1')->fetch_assoc()['cnt'] ?? 0);

$recentOrders = [];
$recentRes = $conn->query("
    SELECT
        o.order_id,
        o.total_amount,
        o.status,
        o.take_option,
        o.order_time,
        u.name AS customer_name,
        GROUP_CONCAT(CONCAT(oi.quantity, 'x ', m.name) SEPARATOR ', ') AS items_summary
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN order_items oi ON oi.order_id = o.order_id
    LEFT JOIN menu_items m ON m.item_id = oi.item_id
    WHERE o.status IN ('pending', 'preparing', 'ready')
    GROUP BY o.order_id, o.total_amount, o.status, o.take_option, o.order_time, u.name
    ORDER BY o.order_time ASC
    LIMIT 8
");
while ($r = $recentRes->fetch_assoc()) {
    $recentOrders[] = $r;
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="card-header">
  <div>
    <h1 style="margin:0 0 4px 0;">Kitchen &amp; Admin Dashboard</h1>
    <p class="muted" style="margin:0;">Welcome back, <?php echo e(current_user_name()); ?>.</p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a class="btn btn-primary btn-sm" href="<?php echo e(app_url('admin/orders_manage.php')); ?>">Manage Orders</a>
    <a class="btn btn-secondary btn-sm" href="<?php echo e(app_url('admin/menu_manage.php')); ?>">Manage Menu</a>
  </div>
</div>

<div class="grid-4" style="margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-label">Pending Orders</div>
    <div class="stat-value" style="color:var(--warning);"><?php echo (int) ($statsRow['pending_cnt'] ?? 0); ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">In Kitchen / Ready</div>
    <div class="stat-value" style="color:var(--info);"><?php echo (int) ($statsRow['active_cnt'] ?? 0); ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Today's Revenue</div>
    <div class="stat-value" style="color:var(--success);font-size:1.45rem;"><?php echo e(format_currency($statsRow['today_income'] ?? 0)); ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Active Menu Dishes</div>
    <div class="stat-value"><?php echo $menuAvailable; ?></div>
  </div>
</div>

<div class="grid-2" style="margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-label">Completed Orders (All Time)</div>
    <div class="stat-value"><?php echo (int) ($statsRow['completed_cnt'] ?? 0); ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Total Revenue (Completed)</div>
    <div class="stat-value" style="color:var(--primary-dark);"><?php echo e(format_currency($statsRow['total_income'] ?? 0)); ?></div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3>Active Kitchen Queue</h3>
    <a class="btn btn-secondary btn-sm" href="<?php echo e(app_url('admin/orders_manage.php')); ?>">View All Orders &rarr;</a>
  </div>

  <?php if (empty($recentOrders)): ?>
    <p class="muted" style="margin:0;">No pending or in-progress orders right now.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Option</th>
            <th>Status</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $o): ?>
            <tr>
              <td><strong>#<?php echo (int) $o['order_id']; ?></strong></td>
              <td><?php echo e($o['customer_name']); ?></td>
              <td><?php echo e($o['items_summary'] ?: '—'); ?></td>
              <td><span class="badge badge-muted"><?php echo e(ucfirst($o['take_option'])); ?></span></td>
              <td><span class="badge badge-<?php echo e($o['status']); ?>"><?php echo e(order_status_label((string) $o['status'])); ?></span></td>
              <td><strong><?php echo e(format_currency($o['total_amount'])); ?></strong></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
