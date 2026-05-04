<?php
session_start();
include "../config/database.php";

// Check if user is logged in and is admin/staff
if(!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin' && ($_SESSION['user_role'] ?? '') !== 'staff')){
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
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
    <div class="card">
        <h2>Admin Dashboard</h2>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>

        <?php
        // show pending orders summary
        $res = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE status='pending'");
        $pending = $res->fetch_assoc()['cnt'];
        
        $res2 = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE status='completed'");
        $completed = $res2->fetch_assoc()['cnt'];
        
        $res3 = $conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE status='completed'");
        $total_income = $res3->fetch_assoc()['total'] ?? 0;
        ?>

        <div style="margin: 20px 0;">
            <p><strong>Pending Orders:</strong> <?php echo $pending; ?></p>
            <p><strong>Completed Orders:</strong> <?php echo $completed; ?></p>
            <p><strong>Total Income:</strong> Rs. <?php echo number_format($total_income, 2); ?></p>
        </div>

        <div style="margin: 20px 0;">
            <a href="menu_manage.php" class="btn" style="display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; margin-right:10px;">Manage Menu</a>
            <a href="orders_manage.php" class="btn" style="display:inline-block; padding:10px 20px; background:#28a745; color:white; text-decoration:none;">Manage Orders</a>
        </div>
    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> University Canteen</p>
    </div>
</footer>

</body>
</html>
