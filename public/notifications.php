<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
ensure_logged_in();

$userId = current_user_id();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_valid_post('public/notifications.php');
    $stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'All notifications marked as read.');
    redirect_to('public/notifications.php');
}

$pageTitle = 'Notifications';
$activeNav = 'notifications';

$notifications = [];
$stmt = $conn->prepare('SELECT message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card-header">
  <div>
    <h1 style="margin:0 0 4px 0;">Notifications</h1>
    <p class="muted" style="margin:0;">Order status updates from the canteen kitchen.</p>
  </div>
  <?php if (!empty($notifications)): ?>
    <form method="POST">
      <?php echo csrf_field(); ?>
      <button type="submit" class="btn btn-secondary btn-sm">Mark All as Read</button>
    </form>
  <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
  <div class="card empty-state">
    <h3 style="margin-top:0;">No notifications yet</h3>
    <p>You will see alerts here as soon as you place an order or the kitchen updates its status.</p>
  </div>
<?php else: ?>
  <div class="card">
    <?php foreach ($notifications as $n): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);">
        <div>
          <?php if ((int) $n['is_read'] === 0): ?>
            <span class="badge badge-pending" style="margin-right:8px;">New</span>
          <?php endif; ?>
          <span><?php echo e($n['message']); ?></span>
        </div>
        <small class="muted" style="white-space:nowrap;"><?php echo e($n['created_at'] ?? ''); ?></small>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
