<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

ensure_logged_in();
require_valid_post('public/cart.php');

$userId = current_user_id();
$mode = (string) ($_POST['mode'] ?? '');
$takeOption = in_array($_POST['take_option'] ?? '', ['takeaway', 'dine-in'], true)
    ? (string) $_POST['take_option']
    : 'takeaway';
$notes = mb_substr(trim((string) ($_POST['notes'] ?? '')), 0, 255);

// Determine items to order: full session cart OR direct single-item submission
$requestedItems = [];
if ($mode === 'cart') {
    $requestedItems = cart_items();
} elseif (isset($_POST['item_id'])) {
    $itemId = (int) $_POST['item_id'];
    $qty = (int) ($_POST['quantity'] ?? 1);
    if ($itemId > 0 && $qty >= 1 && $qty <= 50) {
        $requestedItems[$itemId] = $qty;
    }
} else {
    $requestedItems = cart_items();
}

if (empty($requestedItems)) {
    set_flash('warning', 'Your cart is empty or invalid quantity was provided.');
    redirect_to('public/menu.php');
}

$ids = array_keys($requestedItems);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$stmt = $conn->prepare("SELECT item_id, name, price, prep_minutes, is_available FROM menu_items WHERE item_id IN ($placeholders)");
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$res = $stmt->get_result();
$dbItems = [];
while ($row = $res->fetch_assoc()) {
    $dbItems[(int) $row['item_id']] = $row;
}
$stmt->close();

$orderLines = [];
$totalAmount = 0.0;
$prepTime = 5;
$totalQty = 0;

foreach ($requestedItems as $itemId => $qty) {
    if ($qty < 1 || $qty > 50 || !isset($dbItems[$itemId]) || (int) $dbItems[$itemId]['is_available'] !== 1) {
        set_flash('error', 'One or more selected items are no longer available. Please review your cart.');
        redirect_to('public/cart.php');
    }
    $item = $dbItems[$itemId];
    $price = (float) $item['price'];
    $totalAmount += $price * $qty;
    $totalQty += $qty;
    $prepTime = max($prepTime, (int) ($item['prep_minutes'] ?? 10));
    $orderLines[] = [
        'item_id' => $itemId,
        'quantity' => $qty,
        'price' => $price,
    ];
}

if ($totalQty > 5) {
    $prepTime = max($prepTime, 20);
}

$conn->begin_transaction();

try {
    $status = 'pending';
    $notesParam = $notes !== '' ? $notes : null;

    $orderStmt = $conn->prepare(
        'INSERT INTO orders (user_id, total_amount, status, take_option, preparation_time, notes) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $orderStmt->bind_param('idssis', $userId, $totalAmount, $status, $takeOption, $prepTime, $notesParam);
    $orderStmt->execute();
    $orderId = (int) $orderStmt->insert_id;
    $orderStmt->close();

    $lineStmt = $conn->prepare('INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)');
    foreach ($orderLines as $line) {
        $lineStmt->bind_param('iiid', $orderId, $line['item_id'], $line['quantity'], $line['price']);
        $lineStmt->execute();
    }
    $lineStmt->close();

    $msg = "Your order #$orderId (" . format_currency($totalAmount) . ') has been placed and is pending confirmation.';
    $notifStmt = $conn->prepare('INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)');
    $notifStmt->bind_param('is', $userId, $msg);
    $notifStmt->execute();
    $notifStmt->close();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    set_flash('error', 'Could not place your order right now. Please try again.');
    redirect_to('public/cart.php');
}

if ($mode === 'cart' || !isset($_POST['item_id'])) {
    cart_clear();
}

set_flash('success', "Order #$orderId placed successfully!");
redirect_to('public/order.php?id=' . $orderId);
