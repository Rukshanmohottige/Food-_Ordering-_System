<?php include "../includes/header.php"; ?>
<style>
  .landing {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 80px 20px;
    min-height: 70vh;
    background: url('../assets/images/canteen-bg.jpg') no-repeat center center;
    background-size: cover;
    color: #fff;
  }
  .landing h1 {
    font-size: 48px;
    margin-bottom: 20px;
    text-shadow: 2px 2px 5px rgba(0,0,0,0.6);
  }
  .landing p {
    font-size: 20px;
    margin-bottom: 30px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
  }
  .btn-primary {
    display: inline-block;
    padding: 12px 30px;
    font-size: 18px;
    background: #28a745;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    transition: 0.3s;
  }
  .btn-primary:hover {
    background: #218838;
  }
</style>

<div class="landing">
  <h1>Welcome to the University Canteen</h1>
  <p>Quickly pre-order your meals and avoid long queues. Fresh food, just a click away!</p>
  <a href="menu.php" class="btn-primary">View Menu</a>
</div>

<div class="card" style="margin: 40px auto; max-width: 800px; padding: 20px; text-align:center;">
  <h2>How it Works</h2>
  <p>1. Browse the menu<br>
     2. Select your meals<br>
     3. Pre-order and pick up at your convenience</p>
</div>

<?php include "../includes/footer.php"; ?>
