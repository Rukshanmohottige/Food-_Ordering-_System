<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? 'University Canteen';
$activeNav = $activeNav ?? '';
$cartCount = cart_count();
$unreadNotifs = (is_logged_in() && isset($conn) && $conn instanceof mysqli)
    ? get_unread_notification_count($conn, current_user_id())
    : 0;
$flashes = consume_flashes();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e($pageTitle); ?> — University Canteen</title>
  <link rel="stylesheet" href="<?php echo e(app_url('assets/css/style.css')); ?>?v=2">
</head>
<body>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="brand" href="<?php echo e(app_url('public/index.php')); ?>">
      <span class="brand-icon" aria-hidden="true">🍽️</span>
      <span>University Canteen</span>
    </a>

    <button class="nav-toggle" type="button" aria-label="Toggle navigation" onclick="document.getElementById('mainNav').classList.toggle('open')">
      ☰
    </button>

    <nav id="mainNav" class="nav-links">
      <a class="<?php echo $activeNav === 'home' ? 'active' : ''; ?>" href="<?php echo e(app_url('public/index.php')); ?>">Home</a>
      <a class="<?php echo $activeNav === 'menu' ? 'active' : ''; ?>" href="<?php echo e(app_url('public/menu.php')); ?>">Menu</a>

      <?php if (is_logged_in()): ?>
        <a class="<?php echo $activeNav === 'cart' ? 'active' : ''; ?>" href="<?php echo e(app_url('public/cart.php')); ?>">
          Cart <?php if ($cartCount > 0): ?><span class="pill-count"><?php echo $cartCount; ?></span><?php endif; ?>
        </a>
        <a class="<?php echo $activeNav === 'orders' ? 'active' : ''; ?>" href="<?php echo e(app_url('public/my_orders.php')); ?>">My Orders</a>
        <a class="<?php echo $activeNav === 'notifications' ? 'active' : ''; ?>" href="<?php echo e(app_url('public/notifications.php')); ?>">
          Alerts <?php if ($unreadNotifs > 0): ?><span class="pill-count pill-alert"><?php echo $unreadNotifs; ?></span><?php endif; ?>
        </a>
        <?php if (is_admin_or_staff()): ?>
          <a href="<?php echo e(app_url('admin/admin_dashboard.php')); ?>">Admin Panel</a>
        <?php endif; ?>
        <span class="nav-user" title="Signed in account">
          <span aria-hidden="true">👤</span>
          <span><?php echo e(current_user_name()); ?></span>
        </span>
        <form action="<?php echo e(app_url('public/logout.php')); ?>" method="POST" class="nav-logout-form">
          <?php echo csrf_field(); ?>
          <button type="submit" class="btn-logout">Logout</button>
        </form>
      <?php else: ?>
        <a class="<?php echo $activeNav === 'login' ? 'active' : ''; ?>" href="<?php echo e(app_url('public/login.php')); ?>">Login</a>
        <a class="nav-cta <?php echo $activeNav === 'register' ? 'active' : ''; ?>" href="<?php echo e(app_url('public/register.php')); ?>">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container page-main">
  <?php foreach ($flashes as $flash): ?>
    <div class="alert alert-<?php echo e($flash['type']); ?>" data-auto-dismiss="true">
      <span><?php echo e($flash['message']); ?></span>
      <button type="button" class="alert-close" aria-label="Close" onclick="this.parentElement.remove()">×</button>
    </div>
  <?php endforeach; ?>
