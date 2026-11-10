<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_admin_or_staff()) {
    redirect_to('admin/admin_dashboard.php');
}

$oldEmail = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $pass = (string) ($_POST['password'] ?? '');
    $oldEmail = $email;

    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please submit the form again.');
    } elseif ($email === '' || $pass === '') {
        set_flash('error', 'Please enter both email and password.');
    } else {
        $stmt = $conn->prepare('SELECT user_id, name, password, user_role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $u = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($u && verify_and_upgrade_password($conn, (int) $u['user_id'], $pass, (string) $u['password'])) {
            if (in_array($u['user_role'], ['admin', 'staff'], true)) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $u['user_id'];
                $_SESSION['user_name'] = (string) $u['name'];
                $_SESSION['user_role'] = (string) $u['user_role'];

                set_flash('success', 'Signed in as ' . $u['name'] . ' (' . ucfirst((string) $u['user_role']) . ').');
                redirect_to('admin/admin_dashboard.php');
            }
            set_flash('error', 'Access denied. Please use the Student / User login tab for customer accounts.');
        } else {
            set_flash('error', 'Invalid email or password.');
        }
    }
}

$flashes = consume_flashes();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Staff &amp; Admin Login — University Canteen</title>
  <link rel="stylesheet" href="<?php echo e(app_url('assets/css/style.css')); ?>?v=2">
</head>
<body>
<main class="container page-main">
  <?php foreach ($flashes as $f): ?>
    <div class="alert alert-<?php echo e($f['type']); ?>" data-auto-dismiss="true" style="max-width:860px;margin:0 auto 18px;">
      <span><?php echo e($f['message']); ?></span>
      <button type="button" class="alert-close" aria-label="Close" onclick="this.parentElement.remove()">×</button>
    </div>
  <?php endforeach; ?>

  <div class="auth-shell">
    <aside class="auth-banner auth-banner-admin">
      <span class="hero-badge">Kitchen &amp; Management</span>
      <h2>Staff &amp; Admin Portal</h2>
      <p>Update menu availability, move live orders from Preparing to Ready, and view daily sales reports.</p>
      <ul class="auth-perks">
        <li>Live kitchen order queue</li>
        <li>Menu item CRUD &amp; sold-out toggles</li>
        <li>Daily sales &amp; top-dish analytics</li>
      </ul>
    </aside>

    <section class="auth-panel">
      <div class="auth-tabs" role="tablist" aria-label="Login portal switcher">
        <a class="auth-tab" href="<?php echo e(app_url('public/login.php')); ?>">Student / User</a>
        <a class="auth-tab active" href="<?php echo e(app_url('admin/login.php')); ?>">Staff / Admin</a>
      </div>

      <h1 class="auth-title">Admin Sign In</h1>
      <p class="muted" style="margin-top:0;margin-bottom:20px;">Authorized canteen staff and administrators only.</p>

      <form method="POST" autocomplete="on">
        <?php echo csrf_field(); ?>
        <div class="form-group">
          <label for="adminEmail">Staff / Admin Email</label>
          <input id="adminEmail" type="email" name="email" value="<?php echo e($oldEmail); ?>" placeholder="staff@canteen.com" required autofocus>
        </div>

        <div class="form-group">
          <label for="adminPassword">Password</label>
          <div class="password-wrap">
            <input id="adminPassword" type="password" name="password" placeholder="Enter password" required>
            <button type="button" class="toggle-pass-btn" data-toggle-password="adminPassword" aria-label="Show password">Show</button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;">Sign In to Admin Panel</button>
      </form>

      <div style="margin-top:18px;text-align:center;">
        <a class="btn btn-secondary btn-sm" href="<?php echo e(app_url('public/index.php')); ?>">&larr; Back to Storefront</a>
      </div>
    </section>
  </div>
</main>
<script src="<?php echo e(app_url('assets/js/app.js')); ?>"></script>
</body>
</html>
