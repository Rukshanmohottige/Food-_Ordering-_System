<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_valid_post('public/register.php');

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$pass = (string) ($_POST['password'] ?? '');
$confirm = (string) ($_POST['confirm_password'] ?? '');

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    set_flash('error', 'Please enter your full name (2–100 characters).');
    redirect_to('public/register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Please enter a valid email address.');
    redirect_to('public/register.php');
}

if (strlen($pass) < 6) {
    set_flash('error', 'Password must be at least 6 characters.');
    redirect_to('public/register.php');
}

if ($confirm !== '' && $pass !== $confirm) {
    set_flash('error', 'Passwords do not match.');
    redirect_to('public/register.php');
}

$hash = hash_password($pass);

try {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, user_role) VALUES (?, ?, ?, 'student')");
    $stmt->bind_param('sss', $name, $email, $hash);
    $stmt->execute();
    $stmt->close();

    set_flash('success', 'Account created successfully! Please log in.');
    redirect_to('public/login.php');
} catch (mysqli_sql_exception $e) {
    if ((int) $e->getCode() === 1062) {
        set_flash('warning', 'This email is already registered. Please log in instead.');
        redirect_to('public/login.php');
    }
    set_flash('error', 'Registration failed. Please try again.');
    redirect_to('public/register.php');
}
