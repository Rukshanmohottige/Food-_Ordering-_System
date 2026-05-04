<?php
session_start();
include "../config/database.php";

// Check if user is logged in and is admin/staff
if(!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin' && ($_SESSION['user_role'] ?? '') !== 'staff')){
    header("Location: login.php");
    exit;
}

if(isset($_GET['update']) && isset($_GET['status']) ){
    $oid = intval($_GET['update']);
    $status = $_GET['status'];
    $valid_statuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];
    
    if(in_array($status, $valid_statuses)){
        $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
        $stmt->bind_param('si',$status,$oid);
        $stmt->execute();
        echo "<p style='color:green;'>Order updated to: $status</p>";
    }
}

$res = $conn->query("SELECT o.*, u.name FROM orders o JOIN users u ON o.user_id=u.user_id ORDER BY o.order_time DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="menu_manage.php">Manage Menu</a>
            <a href="orders_manage.php">Manage Orders</a>
            <a href="admin_logout.php">
                Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)
            </a>
        </nav>
    </div>
</header>

<main class="container">
<div class="card"><h2>Manage Orders</h2>
<?php while($o=$res->fetch_assoc()): ?>
  <div class="menu-item">
    <div>
      <strong>Order #<?php echo $o['order_id']; ?></strong><br>
      <small><?php echo htmlspecialchars($o['name']); ?> — <?php echo $o['order_time']; ?></small><br>
      <small><strong>Status: <?php echo ucfirst($o['status']); ?></strong></small><br>
      <small>Amount: Rs <?php echo number_format($o['total_amount'], 2); ?></small>
    </div>
    <div>
      <a href="?update=<?php echo $o['order_id']; ?>&status=preparing" style="text-decoration:none; padding:5px 10px; background:#ffc107; color:black; border-radius:3px; display:inline-block; margin:2px;">Preparing</a>
      <a href="?update=<?php echo $o['order_id']; ?>&status=ready" style="text-decoration:none; padding:5px 10px; background:#17a2b8; color:white; border-radius:3px; display:inline-block; margin:2px;">Ready</a>
      <a href="?update=<?php echo $o['order_id']; ?>&status=completed" style="text-decoration:none; padding:5px 10px; background:#28a745; color:white; border-radius:3px; display:inline-block; margin:2px;">Complete</a>
      <a href="?update=<?php echo $o['order_id']; ?>&status=cancelled" style="text-decoration:none; padding:5px 10px; background:#dc3545; color:white; border-radius:3px; display:inline-block; margin:2px;">Cancel</a>
    </div>
  </div>
<?php endwhile; ?>
</div>
</main>

<footer class="site-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> University Canteen</p>
    </div>
</footer>

</body>
</html>
