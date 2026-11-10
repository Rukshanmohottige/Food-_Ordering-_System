<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
ensure_super_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_valid_post('admin/admin_register.php');

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $pass = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    $role = in_array($_POST['user_role'] ?? '', ['staff', 'admin'], true)
        ? (string) $_POST['user_role']
        : 'staff';

    if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Please enter a valid name and email address.');
        redirect_to('admin/admin_register.php');
    }

    if (strlen($pass) < 6) {
        set_flash('error', 'Password must be at least 6 characters.');
        redirect_to('admin/admin_register.php');
    }

    if ($pass !== $confirm) {
        set_flash('error', 'Passwords do not match.');
        redirect_to('admin/admin_register.php');
    }

    $hash = hash_password($pass);

    try {
        $stmt = $conn->prepare('INSERT INTO users (name, email, password, user_role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $hash, $role);
        $stmt->execute();
        $stmt->close();
        set_flash('success', ucfirst($role) . " account created for $email.");
    } catch (mysqli_sql_exception $e) {
        if ((int) $e->getCode() === 1062) {
            set_flash('warning', 'That email address is already registered.');
        } else {
            set_flash('error', 'Failed to create account.');
        }
    }
    redirect_to('admin/admin_register.php');
}

$staffList = [];
$res = $conn->query("SELECT user_id, name, email, user_role, created_at FROM users WHERE user_role IN ('admin', 'staff') ORDER BY user_role ASC, created_at DESC");
while ($row = $res->fetch_assoc()) {
    $staffList[] = $row;
}

$pageTitle = 'Staff & Admins';
$activeAdminNav = 'users';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="card-header">
  <h1 style="margin:0;">Staff &amp; Admin Accounts</h1>
</div>

<div class="grid-2" style="grid-template-columns: 1fr 1.4fr; align-items:start;">
  <div class="card">
    <h3 style="margin-top:0;">Create Staff / Admin Account</h3>
    <form method="POST">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label for="name">Full Name</label>
        <input id="name" type="text" name="name" placeholder="Kitchen Staff Name" required>
      </div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input id="email" type="email" name="email" placeholder="staff@canteen.com" required>
      </div>
      <div class="form-group">
        <label for="user_role">Role</label>
        <select id="user_role" name="user_role">
          <option value="staff">Staff (Kitchen &amp; Menu)</option>
          <option value="admin">Admin (Full Access)</option>
        </select>
      </div>
      <div class="form-group">
        <label for="password">Password (min 6 characters)</label>
        <input id="password" type="password" name="password" minlength="6" required>
      </div>
      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input id="confirm_password" type="password" name="confirm_password" minlength="6" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Create Account</button>
    </form>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Existing Staff &amp; Admins</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($staffList as $u): ?>
            <tr>
              <td><strong><?php echo e($u['name']); ?></strong></td>
              <td><?php echo e($u['email']); ?></td>
              <td><span class="badge <?php echo $u['user_role'] === 'admin' ? 'badge-ready' : 'badge-preparing'; ?>"><?php echo e(strtoupper((string) $u['user_role'])); ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
