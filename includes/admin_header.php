<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
ensure_admin();

$pageTitle = $pageTitle ?? 'Admin Panel';
$activeAdminNav = $activeAdminNav ?? '';
$flashes = consume_flashes();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e($pageTitle); ?> — Canteen Admin</title>
  <link rel="stylesheet" href="<?php echo e(app_url('assets/css/style.css')); ?>?v=2">
</head>
<body class="admin-body">
<header class="site-header admin-header">
  <div class="container nav-wrap">
    <a class="brand" href="<?php echo e(app_url('admin/admin_dashboard.php')); ?>">
      <span class="brand-icon" aria-hidden="true">🧑‍🍳</span>
      <span>Canteen Admin</span>
    </a>

    <button class="nav-toggle" type="button" aria-label="Toggle navigation" onclick="document.getElementById('adminNav').classList.toggle('open')">
      ☰
    </button>

    <nav id="adminNav" class="nav-links">
      <a class="<?php echo $activeAdminNav === 'dashboard' ? 'active' : ''; ?>" href="<?php echo e(app_url('admin/admin_dashboard.php')); ?>">Dashboard</a>
      <a class="<?php echo $activeAdminNav === 'orders' ? 'active' : ''; ?>" href="<?php echo e(app_url('admin/orders_manage.php')); ?>">Orders</a>
      <a class="<?php echo $activeAdminNav === 'menu' ? 'active' : ''; ?>" href="<?php echo e(app_url('admin/menu_manage.php')); ?>">Menu</a>
      <a class="<?php echo $activeAdminNav === 'reports' ? 'active' : ''; ?>" href="<?php echo e(app_url('admin/reports.php')); ?>">Reports</a>
      <?php if (is_super_admin()): ?>
        <a class="<?php echo $activeAdminNav === 'users' ? 'active' : ''; ?>" href="<?php echo e(app_url('admin/admin_register.php')); ?>">Staff &amp; Admins</a>
      <?php endif; ?>
      <a href="<?php echo e(app_url('public/index.php')); ?>">Storefront</a>
      <span class="nav-user" title="Signed in as <?php echo e(ucfirst(current_user_role())); ?>">
        <span aria-hidden="true">👤</span>
        <span><?php echo e(current_user_name()); ?></span>
      </span>
      <form action="<?php echo e(app_url('admin/admin_logout.php')); ?>" method="POST" class="nav-logout-form">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn-logout">Logout</button>
      </form>
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
