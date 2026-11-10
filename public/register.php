<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    redirect_to('public/index.php');
}

$pageTitle = 'Register';
$activeNav = 'register';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-shell">
  <aside class="auth-banner">
    <span class="hero-badge">University Canteen</span>
    <h2>Create Your Account</h2>
    <p>Join in under a minute to start pre-ordering breakfast, lunch, snacks, and drinks without waiting in line.</p>
    <ul class="auth-perks">
      <li>Order for dine-in or takeaway</li>
      <li>Track live kitchen updates</li>
      <li>Save time between lectures</li>
    </ul>
  </aside>

  <section class="auth-panel">
    <h1 class="auth-title">Student Registration</h1>
    <p class="muted" style="margin-top:0;margin-bottom:20px;">Fill in your details below to get started.</p>

    <form action="<?php echo e(app_url('actions/user_register.php')); ?>" method="POST">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label for="name">Full Name</label>
        <input id="name" type="text" name="name" placeholder="e.g., Kasun Perera" minlength="2" maxlength="100" required autofocus>
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input id="email" type="email" name="email" placeholder="you@university.edu" maxlength="150" required>
      </div>

      <div class="form-group">
        <label for="regPassword">Password (minimum 6 characters)</label>
        <div class="password-wrap">
          <input id="regPassword" type="password" name="password" placeholder="Create a password" minlength="6" required>
          <button type="button" class="toggle-pass-btn" data-toggle-password="regPassword" aria-label="Show password">Show</button>
        </div>
      </div>

      <div class="form-group">
        <label for="regConfirmPassword">Confirm Password</label>
        <div class="password-wrap">
          <input id="regConfirmPassword" type="password" name="confirm_password" placeholder="Repeat password" minlength="6" required>
          <button type="button" class="toggle-pass-btn" data-toggle-password="regConfirmPassword" aria-label="Show password">Show</button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;">Create Account</button>
    </form>

    <p style="margin:18px 0 0;text-align:center;" class="muted">
      Already registered? <a href="<?php echo e(app_url('public/login.php')); ?>"><strong>Sign in here</strong></a>
    </p>
  </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
