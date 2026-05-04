<?php
ob_start(); // start output buffering to prevent header errors
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$err = ''; // initialize error message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    include "../config/database.php"; // make sure this path is correct

    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    // Hash password using SHA-256 to match database
    $hash = hash('sha256', $pass);

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("
        SELECT user_id, name, user_role 
        FROM users 
        WHERE email = ? AND password = ? 
        LIMIT 1
    ");

    $stmt->bind_param("ss", $email, $hash); // use hashed password
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $u = $result->fetch_assoc();

        // Save user data to session
        $_SESSION['user_id']   = $u['user_id'];
        $_SESSION['user_name'] = $u['name'];
        $_SESSION['user_role'] = $u['user_role'];

        // Role-based redirect
        if ($u['user_role'] == 'admin') {
            header("Location: admin_dashboard.php");
            exit;
        } elseif ($u['user_role'] == 'staff') {
            header("Location: dashboard.php");
            exit;
        } else {
            $err = "Unauthorized user role.";
        }

    } else {
        $err = "Invalid email or password.";
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
  <div class="card">
    <h2>Login</h2>

    <?php if (!empty($err)) echo "<p style='color:red'>$err</p>"; ?>

    <form method="POST">
      <input type="email" name="email" placeholder="Enter Email" required>
      <input type="password" name="password" placeholder="Enter Password" required>
      <button type="submit">Login</button>
    </form>

    <div style="margin-top: 20px; text-align: center;">
      <a href="../public/index.php" style="background:#28a745; color:white; padding:10px 25px; border-radius:4px; text-decoration:none;">← Back to Home</a>
    </div>
  </div>
</div>

</body>
</html>
