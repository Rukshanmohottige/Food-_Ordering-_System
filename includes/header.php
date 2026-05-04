<?php
if (session_status() == PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>University Canteen</title>
  <link rel="stylesheet" href="/food_order_system/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container">
    <h1><a href="/food_order_system/public/index.php">Canteen Food Order</a></h1>
    <nav>
      <a href="/food_order_system/public/menu.php">Menu</a>

      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="/food_order_system/public/my_orders.php">My Orders</a>
        <a href="/food_order_system/public/logout.php">
          Logout (<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ""; ?>)
        </a>
      <?php else: ?>
        <a href="/food_order_system/public/register.php">Register</a>
        <a href="/food_order_system/public/login.php">Login</a>
      <?php endif; ?>

      <a href="/food_order_system/admin/login.php">Admin</a>
    </nav>
  </div>
</header>
<main class="container">
