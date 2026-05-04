<?php
include "../config/database.php";
include "../includes/header.php";
$sql = "SELECT * FROM menu_items WHERE is_available=1 ORDER BY name";
$res = $conn->query($sql);
?>
<div class="card">
  <h2>Menu</h2>
  <?php while($row = $res->fetch_assoc()): ?>
    <div class="menu-item">
      <div>
        <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
        <small class="gray"><?php echo htmlspecialchars($row['description']); ?></small><br>
        <small>Rs <?php echo number_format($row['price'],2); ?></small>
      </div>
      <div>
        <form action="../actions/place_order.php" method="POST" style="text-align:right">
          <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
          <input type="hidden" name="quantity" value="1">
          <label>Option:</label>
          <select name="take_option">
            <option value="takeaway">Takeaway</option>
            <option value="dine-in">Dine-in</option>
          </select>
          <button type="submit">Order Now</button>
        </form>
      </div>
    </div>
  <?php endwhile; ?>
</div>
<?php include "../includes/footer.php"; ?>
