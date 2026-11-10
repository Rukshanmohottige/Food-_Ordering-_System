<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/bootstrap.php';

// Security guard: prevent resetting or creating admin accounts once an admin already exists.
$adminCount = (int) ($conn->query("SELECT COUNT(*) AS cnt FROM users WHERE user_role = 'admin'")->fetch_assoc()['cnt'] ?? 0);

if ($adminCount > 0 && PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Setup Locked</title>';
    echo '<link rel="stylesheet" href="' . e(app_url('assets/css/style.css')) . '"></head><body><main class="container page-main"><div class="card auth-card">';
    echo '<h2>Setup Already Completed</h2>';
    echo '<p>An administrator account already exists. For security, this setup script is locked.</p>';
    echo '<a class="btn btn-primary" href="' . e(app_url('admin/login.php')) . '">Go to Admin Login</a>';
    echo '</div></main></body></html>';
    exit;
}

$created = [];

$defaults = [
    ['Admin User', 'admin@canteen.com', 'admin123', 'admin'],
    ['Staff User', 'staff@canteen.com', 'admin123', 'staff'],
    ['Student Demo', 'student@canteen.com', 'student123', 'student'],
];

foreach ($defaults as [$name, $email, $plainPass, $role]) {
    $check = $conn->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
    $check->bind_param('s', $email);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;
    $check->close();

    if (!$exists) {
        $passHash = hash_password($plainPass);
        $ins = $conn->prepare('INSERT INTO users (name, email, password, user_role) VALUES (?, ?, ?, ?)');
        $ins->bind_param('ssss', $name, $email, $passHash, $role);
        $ins->execute();
        $ins->close();
        $created[] = [$role, $email];
    }
}

if (PHP_SAPI === 'cli') {
    echo empty($created) ? "Default accounts already exist.\n" : "Initial admin/staff accounts created.\n";
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Initial Admin Setup</title>
  <link rel="stylesheet" href="<?php echo e(app_url('assets/css/style.css')); ?>">
</head>
<body>
<main class="container page-main">
  <div class="card auth-card">
    <h2 style="margin-top:0;color:var(--success);">Initial Setup Complete</h2>
    <?php if (empty($created)): ?>
      <p>Default accounts already exist in the database.</p>
    <?php else: ?>
      <p>Created initial accounts (change the default password after logging in):</p>
      <ul>
        <li><strong>Admin:</strong> admin@canteen.com / <code>admin123</code></li>
        <li><strong>Staff:</strong> staff@canteen.com / <code>admin123</code></li>
        <li><strong>Student Demo:</strong> student@canteen.com / <code>student123</code></li>
      </ul>
    <?php endif; ?>
    <p class="muted">You may also delete <code>setup_admin.php</code> after setup.</p>
    <a class="btn btn-primary" href="<?php echo e(app_url('admin/login.php')); ?>">Go to Admin Login</a>
  </div>
</main>
</body>
</html>
