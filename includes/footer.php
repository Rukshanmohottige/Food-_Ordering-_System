</main>
<footer class="site-footer">
  <div class="container footer-wrap">
    <p>&copy; <?php echo date('Y'); ?> University Canteen &bull; Fresh meals, zero queues.</p>
    <div class="footer-links">
      <a href="<?php echo e(app_url('public/menu.php')); ?>">Browse Menu</a>
      <a href="<?php echo e(app_url('admin/login.php')); ?>">Staff / Admin Portal</a>
    </div>
  </div>
</footer>
<script src="<?php echo e(app_url('assets/js/app.js')); ?>"></script>
</body>
</html>
