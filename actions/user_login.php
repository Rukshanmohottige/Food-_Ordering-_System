<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_valid_post('public/login.php');

$email = trim((string) ($_POST['email'] ?? ''));
$pass = (string) ($_POST['password'] ?? '');

if ($email === '' || $pass === '') {
    set_old_input('login_email', $email);
    set_flash('error', 'Please enter both email and password.');
    redirect_to('public/login.php');
}

$stmt = $conn->prepare('SELECT user_id, name, password, user_role FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !verify_and_upgrade_password($conn, (int) $user['user_id'], $pass, (string) $user['password'])) {
    set_old_input('login_email', $email);
    set_flash('error', 'Invalid email or password.');
    redirect_to('public/login.php');
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['user_id'];
$_SESSION['user_name'] = (string) $user['name'];
$_SESSION['user_role'] = (string) $user['user_role'];

set_flash('success', 'Welcome back, ' . $user['name'] . '!');
if (cart_count() > 0) {
    redirect_to('public/cart.php');
}
redirect_to('public/menu.php');
