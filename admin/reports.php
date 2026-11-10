<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
ensure_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_valid_post('admin/reports.php');

    // Sync today's live metrics into daily_reports
    $conn->query("
        INSERT INTO daily_reports (report_date, total_orders, completed_orders, cancelled_orders, total_income)
        SELECT
            CURDATE(),
            COUNT(*),
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END),
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END),
            COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END), 0)
        FROM orders
        WHERE DATE(order_time) = CURDATE()
        ON DUPLICATE KEY UPDATE
            total_orders = VALUES(total_orders),
            completed_orders = VALUES(completed_orders),
            cancelled_orders = VALUES(cancelled_orders),
            total_income = VALUES(total_income)
    ");

    set_flash('success', "Saved today's sales summary to daily_reports.");
    redirect_to('admin/reports.php');
}

$dailyRows = [];
$dailyRes = $conn->query("
    SELECT
        DATE(order_time) AS order_date,
        COUNT(*) AS total_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_orders,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders,
        COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END), 0) AS revenue
    FROM orders
    GROUP BY DATE(order_time)
    ORDER BY order_date DESC
    LIMIT 14
");
while ($row = $dailyRes->fetch_assoc()) {
    $dailyRows[] = $row;
}

$topItems = [];
$topRes = $conn->query("
    SELECT
        m.name,
        m.category,
        SUM(oi.quantity) AS total_qty,
        COALESCE(SUM(oi.quantity * oi.price), 0) AS total_sales
    FROM order_items oi
    JOIN orders o ON o.order_id = oi.order_id
    JOIN menu_items m ON m.item_id = oi.item_id
    WHERE o.status != 'cancelled'
    GROUP BY oi.item_id, m.name, m.category
    ORDER BY total_qty DESC
    LIMIT 10
");
while ($row = $topRes->fetch_assoc()) {
    $topItems[] = $row;
}

$pageTitle = 'Sales Reports';
$activeAdminNav = 'reports';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="card-header">
  <div>
    <h1 style="margin:0 0 4px 0;">Sales &amp; Order Reports</h1>
    <p class="muted" style="margin:0;">Daily revenue breakdown and top-selling dishes.</p>
  </div>
  <form method="POST">
    <?php echo csrf_field(); ?>
    <button type="submit" class="btn btn-primary btn-sm">Save Today's Snapshot</button>
  </form>
</div>

<div class="grid-2" style="align-items:start;">
  <div class="card">
    <h3 style="margin-top:0;">Daily Sales (Last 14 Days)</h3>
    <?php if (empty($dailyRows)): ?>
      <p class="muted">No orders recorded yet.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Orders</th>
              <th>Completed</th>
              <th>Cancelled</th>
              <th>Revenue</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dailyRows as $d): ?>
              <tr>
                <td><strong><?php echo e($d['order_date']); ?></strong></td>
                <td><?php echo (int) $d['total_orders']; ?></td>
                <td><?php echo (int) $d['completed_orders']; ?></td>
                <td><?php echo (int) $d['cancelled_orders']; ?></td>
                <td><strong><?php echo e(format_currency($d['revenue'])); ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Top-Selling Dishes</h3>
    <?php if (empty($topItems)): ?>
      <p class="muted">No dish sales recorded yet.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Dish</th>
              <th>Units Sold</th>
              <th>Sales</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topItems as $item): ?>
              <tr>
                <td>
                  <strong><?php echo e($item['name']); ?></strong><br>
                  <small class="muted"><?php echo e($item['category'] ?? 'Mains'); ?></small>
                </td>
                <td><?php echo (int) $item['total_qty']; ?></td>
                <td><strong><?php echo e(format_currency($item['total_sales'])); ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
