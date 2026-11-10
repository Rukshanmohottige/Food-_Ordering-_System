</main>
<footer class="site-footer">
  <div class="container footer-wrap">
    <p>&copy; <?php echo date('Y'); ?> University Canteen — Admin &amp; Kitchen Portal</p>
    <div class="footer-links">
      <span>Role: <?php echo e(ucfirst(current_user_role())); ?></span>
    </div>
  </div>
</footer>
<script src="<?php echo e(app_url('assets/js/app.js')); ?>"></script>
</body>
</html>
