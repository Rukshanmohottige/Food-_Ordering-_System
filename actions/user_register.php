<?php
session_start();
include "../config/database.php";
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';

if(!$name || !$email || !$pass){
    die("Missing fields");
}

if(strlen($pass) < 6){
    die("Password must be at least 6 characters");
}

$hash = hash('sha256', $pass);
$stmt = $conn->prepare("INSERT INTO users (name,email,password,user_role) VALUES (?,?,?,'student')");
if(!$stmt) die("Database error: " . $conn->error);
$stmt->bind_param('sss',$name,$email,$hash);
if($stmt->execute()){
    $_SESSION['success_message'] = "Registration successful! Please login.";
    header("Location: ../public/login.php?registered=1");
    exit;
}else{
    if(strpos($conn->error, 'Duplicate') !== false){
        die("This email is already registered. <a href='../public/login.php'>Login here</a>");
    }else{
        die("Error: " . htmlspecialchars($conn->error));
    }
}
?>
