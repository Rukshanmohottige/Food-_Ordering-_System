<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

ensure_logged_in();
require_valid_post('public/my_orders.php');

$orderId = (int) ($_POST['order_id'] ?? 0);
$userId = current_user_id();

if ($orderId <= 0) {
    set_flash('error', 'Invalid order ID.');
    redirect_to('public/my_orders.php');
}

$stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ? AND user_id = ? AND status = 'pending'");
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected > 0) {
    $msg = "Order #$orderId was cancelled by you.";
    $notif = $conn->prepare('INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)');
    $notif->bind_param('is', $userId, $msg);
    $notif->execute();
    $notif->close();
    set_flash('info', "Order #$orderId has been cancelled.");
} else {
    set_flash('warning', 'Only your own pending orders can be cancelled.');
}

redirect_to('public/order.php?id=' . $orderId);
