<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Home';
$activeNav = 'home';

$featured = [];
$stmt = $conn->prepare('SELECT item_id, name, category, description, price, prep_minutes, image_url FROM menu_items WHERE is_available = 1 ORDER BY created_at DESC LIMIT 3');
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $featured[] = $row;
}
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<section class="hero">
  <div>
    <span class="hero-badge">University Canteen Pre-Order</span>
    <h1>Skip the Lunch Queue. Order Fresh Food Ahead.</h1>
    <p>Browse today's available meals, customize your cart for dine-in or takeaway, and track your order live from kitchen prep to pickup.</p>
    <div class="hero-actions">
      <a class="btn btn-white" href="<?php echo e(app_url('public/menu.php')); ?>">Explore Full Menu</a>
      <?php if (!is_logged_in()): ?>
        <a class="btn btn-outline-white" href="<?php echo e(app_url('public/register.php')); ?>">Create Account</a>
      <?php else: ?>
        <a class="btn btn-outline-white" href="<?php echo e(app_url('public/my_orders.php')); ?>">Track My Orders</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="hero-visual">
    <div class="hero-emoji" aria-hidden="true">🍛 🍔 🥤</div>
    <p style="margin:0;color:#fff;font-weight:600;">Fast Campus Pickup</p>
    <small style="color:#ffedd5;">Live status alerts when your meal is ready</small>
  </div>
</section>

<section class="grid-3" style="margin-bottom:28px;">
  <div class="card">
    <h3 style="margin-top:0;">1. Browse &amp; Pick</h3>
    <p class="muted" style="margin-bottom:0;">Filter dishes by category or search by name, check prep times, and add multiple items to your cart.</p>
  </div>
  <div class="card">
    <h3 style="margin-top:0;">2. Choose Pickup Style</h3>
    <p class="muted" style="margin-bottom:0;">Pick Takeaway or Dine-in, add optional kitchen notes, and confirm your order in seconds.</p>
  </div>
  <div class="card">
    <h3 style="margin-top:0;">3. Track Live Status</h3>
    <p class="muted" style="margin-bottom:0;">Follow your order from Pending to Preparing to Ready for Pickup with instant in-app alerts.</p>
  </div>
</section>

<?php if (!empty($featured)): ?>
  <section class="card">
    <div class="card-header">
      <h2>Fresh on the Menu</h2>
      <a class="btn btn-secondary btn-sm" href="<?php echo e(app_url('public/menu.php')); ?>">View All Items &rarr;</a>
    </div>
    <div class="menu-grid">
      <?php foreach ($featured as $item): ?>
        <article class="dish-card">
          <div class="dish-media">
            <span class="dish-cat"><?php echo e($item['category'] ?? 'Mains'); ?></span>
            <?php $imgSrc = media_url($item['image_url'] ?? null); ?>
            <?php if ($imgSrc !== null): ?>
              <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($item['name']); ?>">
            <?php else: ?>
              <span aria-hidden="true"><?php echo category_emoji((string) ($item['category'] ?? '')); ?></span>
            <?php endif; ?>
          </div>
          <div class="dish-body">
            <h3 class="dish-title"><?php echo e($item['name']); ?></h3>
            <p class="dish-desc"><?php echo e($item['description'] ?: 'Freshly prepared at the university canteen.'); ?></p>
            <div class="dish-meta">
              <span class="dish-price"><?php echo e(format_currency($item['price'])); ?></span>
              <span class="dish-prep">~<?php echo (int) ($item['prep_minutes'] ?? 10); ?> min</span>
            </div>
            <?php if (is_logged_in()): ?>
              <form action="<?php echo e(app_url('actions/cart.php')); ?>" method="POST" class="qty-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="item_id" value="<?php echo (int) $item['item_id']; ?>">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="redirect" value="public/index.php">
                <button type="submit" class="btn btn-primary" style="width:100%;">Add to Cart</button>
              </form>
            <?php else: ?>
              <a href="<?php echo e(app_url('public/login.php')); ?>" class="btn btn-secondary" style="width:100%;">Login to Order</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
