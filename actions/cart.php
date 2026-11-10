<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

ensure_logged_in();
require_valid_post('public/menu.php');

$action = (string) ($_POST['action'] ?? 'add');
$itemId = (int) ($_POST['item_id'] ?? 0);
$qty = (int) ($_POST['quantity'] ?? 1);
$redirect = (string) ($_POST['redirect'] ?? 'public/cart.php');

// Restrict redirect to safe relative app paths
$allowedRedirects = ['public/index.php', 'public/menu.php', 'public/cart.php'];
if (!in_array($redirect, $allowedRedirects, true)) {
    $redirect = 'public/cart.php';
}

if ($action === 'clear') {
    cart_clear();
    set_flash('info', 'Cart cleared.');
    redirect_to('public/cart.php');
}

if ($action === 'remove') {
    cart_set($itemId, 0);
    set_flash('info', 'Item removed from cart.');
    redirect_to('public/cart.php');
}

if ($itemId <= 0) {
    set_flash('error', 'Invalid menu item.');
    redirect_to($redirect);
}

$stmt = $conn->prepare('SELECT item_id, name, is_available FROM menu_items WHERE item_id = ? LIMIT 1');
$stmt->bind_param('i', $itemId);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item || (int) $item['is_available'] !== 1) {
    set_flash('error', 'That menu item is currently unavailable.');
    redirect_to($redirect);
}

if ($action === 'update') {
    if ($qty < 1) {
        cart_set($itemId, 0);
        set_flash('info', $item['name'] . ' removed from cart.');
    } else {
        cart_set($itemId, min(50, $qty));
        set_flash('success', 'Updated quantity for ' . $item['name'] . '.');
    }
    redirect_to('public/cart.php');
}

// Default: add
$qty = max(1, min(50, $qty));
cart_add($itemId, $qty);
set_flash('success', 'Added ' . $qty . 'x ' . $item['name'] . ' to your cart.');
redirect_to($redirect);
