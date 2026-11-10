<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

ensure_admin();
require_valid_post('admin/orders_manage.php');

$orderId = (int) ($_POST['order_id'] ?? 0);
$status = trim((string) ($_POST['status'] ?? ''));
$filterStatus = trim((string) ($_POST['filter_status'] ?? 'all'));
$validStatuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];

$redirectTarget = 'admin/orders_manage.php';
if ($filterStatus !== 'all' && in_array($filterStatus, $validStatuses, true)) {
    $redirectTarget .= '?status=' . urlencode($filterStatus);
}

if ($orderId <= 0 || !in_array($status, $validStatuses, true)) {
    set_flash('error', 'Invalid order status update request.');
    redirect_to($redirectTarget);
}

$check = $conn->prepare('SELECT user_id, status FROM orders WHERE order_id = ? LIMIT 1');
$check->bind_param('i', $orderId);
$check->execute();
$order = $check->get_result()->fetch_assoc();
$check->close();

if (!$order) {
    set_flash('error', 'Order not found.');
    redirect_to($redirectTarget);
}

$stmt = $conn->prepare('UPDATE orders SET status = ? WHERE order_id = ?');
$stmt->bind_param('si', $status, $orderId);
$stmt->execute();
$stmt->close();

if ($order['status'] !== $status) {
    $customerId = (int) $order['user_id'];
    $msg = "Order #$orderId status updated to: " . order_status_label($status) . '.';
    $notif = $conn->prepare('INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)');
    $notif->bind_param('is', $customerId, $msg);
    $notif->execute();
    $notif->close();
}

set_flash('success', "Order #$orderId updated to " . order_status_label($status) . '.');
redirect_to($redirectTarget);
