<?php
include "../config/database.php";
if($_SERVER['REQUEST_METHOD']=='POST'){
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
    $stmt->bind_param('si',$status,$order_id);
    if($stmt->execute()) echo "OK";
    else echo "ERR";
}
?>
