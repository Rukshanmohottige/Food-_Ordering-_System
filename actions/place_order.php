<?php
session_start();
include "../config/database.php";

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$item_id = intval($_POST['item_id'] ?? 0);
$qty = intval($_POST['quantity'] ?? 1);
$take_option = in_array($_POST['take_option'] ?? '', ['takeaway','dine-in']) 
                ? $_POST['take_option'] 
                : 'takeaway';

// Fetch item price
$stmt = $conn->prepare("SELECT price FROM menu_items WHERE item_id=? AND is_available=1 LIMIT 1");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    die("Item not available");
}

$row = $res->fetch_assoc();
$price = $row['price'];
$total = $price * $qty;

// Create order
$stmt = $conn->prepare("
    INSERT INTO orders (user_id, total_amount, status, take_option, preparation_time)
    VALUES (?, ?, ?, ?, ?)
");

// FIX: define status BEFORE bind_param
$status = "pending";

// preparation time logic
$prep = ($qty > 5) ? 25 : 10;

$stmt->bind_param("idssi", $user_id, $total, $status, $take_option, $prep);

if (!$stmt->execute()) {
    die("Order create error: " . $stmt->error);
}

$order_id = $stmt->insert_id;

// Insert order items
$stmt2 = $conn->prepare("
    INSERT INTO order_items (order_id, item_id, quantity, price)
    VALUES (?, ?, ?, ?)
");
$stmt2->bind_param("iiid", $order_id, $item_id, $qty, $price);
$stmt2->execute();

// Add notification
$msg = "Your order #$order_id has been placed.";
$stmt3 = $conn->prepare("
    INSERT INTO notifications (user_id, message, is_read)
    VALUES (?, ?, 0)
");
$stmt3->bind_param("is", $user_id, $msg);
$stmt3->execute();

// Redirect to order page
header("Location: ../public/order.php?id=" . $order_id);
exit;
?>
