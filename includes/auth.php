<?php
if (session_status() == PHP_SESSION_NONE) session_start();
function ensure_logged_in(){
    if(!isset($_SESSION['user_id'])){
        header("Location: /food_order_system/public/login.php");
        exit;
    }
}
function ensure_admin(){
    if(!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin'){
        header("Location: /food_order_system/admin/login.php");
        exit;
    }
}
?>
