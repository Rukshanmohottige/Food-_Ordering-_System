<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    include "../config/database.php"; // adjust path if needed

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    // Basic validation
    if ($pass !== $confirm_pass) {
        $err = "Passwords do not match.";
    } elseif (empty($name) || empty($email) || empty($pass)) {
        $err = "All fields are required.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $err = "Email is already registered.";
        } else {
            // Hash the password
            $hash = hash('sha256', $pass);

            // Insert new admin user
            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password, user_role, created_at)
                VALUES (?, ?, ?, 'admin', NOW())
            ");
            $stmt->bind_param("sss", $name, $email, $hash);

            if ($stmt->execute()) {
                $success = "Admin registered successfully. You can now <a href='login.php'>login</a>.";
            } else {
                $err = "Registration failed. Please try again.";
            }
        }
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Registration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
  <div class="card">
    <h2>Admin Registration</h2>

    <?php 
    if (!empty($err)) echo "<p style='color:red'>$err</p>"; 
    if (!empty($success)) echo "<p style='color:green'>$success</p>"; 
    ?>

    <form method="POST">
      <input type="text" name="name" placeholder="Enter Name" required>
      <input type="email" name="email" placeholder="Enter Email" required>
      <input type="password" name="password" placeholder="Enter Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <button type="submit">Register</button>
    </form>

    <div style="margin-top: 20px; text-align: center;">
      <a href="login.php" style="background:#28a745; color:white; padding:10px 25px; border-radius:4px; text-decoration:none;">← Back to Login</a>
    </div>
  </div>
</div>

</body>
</html>
