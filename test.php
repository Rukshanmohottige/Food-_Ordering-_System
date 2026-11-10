<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$adminCount = (int) ($conn->query("SELECT COUNT(*) AS cnt FROM users WHERE user_role = 'admin'")->fetch_assoc()['cnt'] ?? 0);
if ($adminCount > 0) {
    ensure_admin();
}

$pageTitle = 'System Diagnostics';
$activeAdminNav = '';

$tables = ['users', 'menu_items', 'orders', 'order_items', 'notifications', 'daily_reports'];
$existingTables = [];
$tRes = $conn->query('SHOW TABLES');
while ($row = $tRes->fetch_row()) {
    $existingTables[] = $row[0];
}

$requiredFiles = [
    'db_structure.sql',
    'config/database.php',
    'includes/bootstrap.php',
    'includes/auth.php',
    'public/index.php',
    'public/menu.php',
    'public/cart.php',
    'public/my_orders.php',
    'public/order.php',
    'public/notifications.php',
    'actions/cart.php',
    'actions/place_order.php',
    'actions/cancel_order.php',
    'actions/update_order_status.php',
    'admin/login.php',
    'admin/admin_dashboard.php',
    'admin/menu_manage.php',
    'admin/orders_manage.php',
    'admin/reports.php',
    'admin/admin_register.php',
];

if ($adminCount > 0) {
    require_once __DIR__ . '/includes/admin_header.php';
} else {
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <title>System Diagnostics</title>
      <link rel="stylesheet" href="<?php echo e(app_url('assets/css/style.css')); ?>">
    </head>
    <body>
    <main class="container page-main">
    <?php
}
?>

<div class="card">
  <h2 style="margin-top:0;">System Health Diagnostics</h2>
  <p class="muted">PHP <?php echo e(PHP_VERSION); ?> &bull; Extensions: mysqli=<?php echo extension_loaded('mysqli') ? 'OK' : 'Missing'; ?>, password_hash=<?php echo function_exists('password_hash') ? 'OK' : 'Missing'; ?></p>

  <h3>Database Tables</h3>
  <div class="table-wrap" style="margin-bottom:20px;">
    <table>
      <thead><tr><th>Table</th><th>Status</th><th>Rows</th></tr></thead>
      <tbody>
        <?php foreach ($tables as $t): ?>
          <?php
          $ok = in_array($t, $existingTables, true);
          $cnt = $ok ? (int) ($conn->query("SELECT COUNT(*) AS cnt FROM `$t`")->fetch_assoc()['cnt'] ?? 0) : 0;
          ?>
          <tr>
            <td><code><?php echo e($t); ?></code></td>
            <td><span class="badge <?php echo $ok ? 'badge-completed' : 'badge-cancelled'; ?>"><?php echo $ok ? 'OK' : 'Missing'; ?></span></td>
            <td><?php echo $cnt; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <h3>Core Project Files</h3>
  <div class="table-wrap">
    <table>
      <thead><tr><th>File</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($requiredFiles as $f): ?>
          <?php $exists = file_exists(__DIR__ . '/' . $f); ?>
          <tr>
            <td><code><?php echo e($f); ?></code></td>
            <td><span class="badge <?php echo $exists ? 'badge-completed' : 'badge-cancelled'; ?>"><?php echo $exists ? 'Present' : 'Missing'; ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
if ($adminCount > 0) {
    require_once __DIR__ . '/includes/admin_footer.php';
} else {
    echo '</main></body></html>';
}
