<?php
include "../config/database.php";
include "../includes/header.php";

if(!isset($_GET['id'])) {
  echo "<div class='card'><p>No order selected.</p></div>";
  include "../includes/footer.php";
  exit;
}
$order_id = intval($_GET['id']);
$sql = "SELECT o.*, u.name FROM orders o JOIN users u ON o.user_id=u.user_id WHERE o.order_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i',$order_id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
if(!$order){
  echo "<div class='card'><p>Order not found.</p></div>";
  include "../includes/footer.php";
  exit;
}
?>
<div class="card">
  <h2>Order #<?php echo $order['order_id']; ?></h2>
  <p>Customer: <?php echo htmlspecialchars($order['name']); ?></p>
  <p>Status: <?php echo htmlspecialchars($order['status']); ?></p>
  <p>Total: Rs <?php echo number_format($order['total_amount'],2); ?></p>
</div>
<?php include "../includes/footer.php"; ?>
