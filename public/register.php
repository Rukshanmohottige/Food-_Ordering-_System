<?php include "../includes/header.php"; ?>
<div class="card">
  <h2>Register</h2>
  <form action="../actions/user_register.php" method="POST">
    <div class="form-group"><input type="text" name="name" placeholder="Full name" required></div>
    <div class="form-group"><input type="email" name="email" placeholder="Email" required></div>
    <div class="form-group"><input type="password" name="password" placeholder="Password" required></div>
    <button type="submit">Register</button>
  </form>
</div>
<?php include "../includes/footer.php"; ?>
