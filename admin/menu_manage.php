<?php
session_start();
include "../config/database.php";

// Check if user is logged in and is admin/staff
if(!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin' && ($_SESSION['user_role'] ?? '') !== 'staff')){
    header("Location: login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD']=='POST'){
    $name = $_POST['name']; $desc = $_POST['description']; $price = floatval($_POST['price']);
    $stmt = $conn->prepare("INSERT INTO menu_items (name,description,price,is_available) VALUES (?,?,?,1)");
    $stmt->bind_param('ssd',$name,$desc,$price);
    $stmt->execute();
    echo "<p style='color:green;'>Item added successfully.</p>";
}

// Handle toggle availability
if(isset($_GET['toggle'])){
    $item_id = intval($_GET['toggle']);
    $stmt = $conn->prepare("UPDATE menu_items SET is_available = NOT is_available WHERE item_id = ?");
    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    header("Location: menu_manage.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manage Menu</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="menu_manage.php">Manage Menu</a>
            <a href="orders_manage.php">Manage Orders</a>
            <a href="admin_logout.php">
                Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)
            </a>
        </nav>
    </div>
</header>

<main class="container">
<div class="card">
  <h2>Manage Menu</h2>
  <form method="POST">
    <div class="form-group"><input type="text" name="name" placeholder="Item name" required></div>
    <div class="form-group"><input type="text" name="description" placeholder="Description"></div>
    <div class="form-group"><input type="text" name="price" placeholder="Price" required></div>
    <button type="submit">Add Item</button>
  </form>
  <hr>
  <h3>Existing items</h3>
  <?php
  $r = $conn->query("SELECT * FROM menu_items ORDER BY created_at DESC");
  while($it = $r->fetch_assoc()){
      $status = $it['is_available'] ? 'Available' : 'Not Available';
      echo "<div class='menu-item'><div><strong>".htmlspecialchars($it['name'])."</strong><br><small>Rs ".number_format($it['price'],2)."</small><br><small style='color:gray;'>Status: $status</small></div>";
      echo "<div><a href='?toggle=".$it['item_id']."' class='secondary'>Toggle</a></div></div>";
  }
  ?>
</div>
</main>

<footer class="site-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> University Canteen</p>
    </div>
</footer>

</body>
</html>
