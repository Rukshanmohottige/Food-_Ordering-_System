<?php include "../includes/header.php"; ?>
<div class="card">
  <h2>Login</h2>
  <form action="../actions/user_login.php" method="POST">
    <div class="form-group"><input type="email" name="email" placeholder="Email" required></div>
    <div class="form-group"><input type="password" name="password" placeholder="Password" required></div>
    <button type="submit">Login</button>
  </form>
</div>
<?php include "../includes/footer.php"; ?>
