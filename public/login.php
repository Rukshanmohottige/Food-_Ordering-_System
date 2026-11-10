<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    redirect_to('public/index.php');
}

$pageTitle = 'Login';
$activeNav = 'login';
$oldEmail = consume_old_input('login_email');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-shell">
  <aside class="auth-banner">
    <span class="hero-badge">University Canteen</span>
    <h2>Welcome Back!</h2>
    <p>Sign in to pre-order meals, customize dine-in or takeaway, and receive live kitchen ready alerts.</p>
    <ul class="auth-perks">
      <li>Quick multi-item cart &amp; checkout</li>
      <li>Real-time order status tracker</li>
      <li>Instant pickup notifications</li>
    </ul>
  </aside>

  <section class="auth-panel">
    <div class="auth-tabs" role="tablist" aria-label="Login portal switcher">
      <a class="auth-tab active" href="<?php echo e(app_url('public/login.php')); ?>">Student / User</a>
      <a class="auth-tab" href="<?php echo e(app_url('admin/login.php')); ?>">Staff / Admin</a>
    </div>

    <h1 class="auth-title">User Login</h1>
    <p class="muted" style="margin-top:0;margin-bottom:20px;">Sign in with your student or customer account.</p>

    <form action="<?php echo e(app_url('actions/user_login.php')); ?>" method="POST" autocomplete="on">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label for="loginEmail">Email Address</label>
        <input id="loginEmail" type="email" name="email" value="<?php echo e($oldEmail); ?>" placeholder="you@university.edu" required autofocus>
      </div>

      <div class="form-group">
        <label for="loginPassword">Password</label>
        <div class="password-wrap">
          <input id="loginPassword" type="password" name="password" placeholder="Enter your password" required>
          <button type="button" class="toggle-pass-btn" data-toggle-password="loginPassword" aria-label="Show password">Show</button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;">Sign In</button>
    </form>

    <p style="margin:18px 0 0;text-align:center;" class="muted">
      New to the canteen? <a href="<?php echo e(app_url('public/register.php')); ?>"><strong>Create an account</strong></a>
    </p>
  </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
