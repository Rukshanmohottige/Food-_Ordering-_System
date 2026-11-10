<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Menu';
$activeNav = 'menu';

$selectedCategory = trim((string) ($_GET['category'] ?? ''));
$searchTerm = trim((string) ($_GET['q'] ?? ''));

$items = [];
$categories = [];
$stmt = $conn->prepare('SELECT item_id, name, category, description, price, prep_minutes, image_url FROM menu_items WHERE is_available = 1 ORDER BY category ASC, name ASC');
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $cat = trim((string) ($row['category'] ?? 'Mains')) ?: 'Mains';
    $row['category'] = $cat;
    $categories[$cat] = true;
    $items[] = $row;
}
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card-header">
  <div>
    <h1 style="margin:0 0 4px 0;">Canteen Menu</h1>
    <p class="muted" style="margin:0;">Pick your dishes, adjust quantities, and check out in one order.</p>
  </div>
  <?php if (!is_logged_in()): ?>
    <a class="btn btn-primary" href="<?php echo e(app_url('public/login.php')); ?>">Login to Order &rarr;</a>
  <?php elseif (cart_count() > 0): ?>
    <a class="btn btn-primary" href="<?php echo e(app_url('public/cart.php')); ?>">
      View Cart (<?php echo cart_count(); ?>) &rarr;
    </a>
  <?php endif; ?>
</div>

<div class="menu-toolbar">
  <div class="filter-tabs" role="tablist" aria-label="Filter menu by category">
    <button type="button" class="filter-tab <?php echo $selectedCategory === '' ? 'active' : ''; ?>" data-filter-category="all">All</button>
    <?php foreach (array_keys($categories) as $cat): ?>
      <button type="button"
              class="filter-tab <?php echo strcasecmp($selectedCategory, $cat) === 0 ? 'active' : ''; ?>"
              data-filter-category="<?php echo e($cat); ?>">
        <?php echo e($cat); ?>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="search-box">
    <input type="search" id="menuSearch" value="<?php echo e($searchTerm); ?>" placeholder="Search dishes..." aria-label="Search dishes">
  </div>
</div>

<?php if (empty($items)): ?>
  <div class="card empty-state">
    <h3 style="margin-top:0;">No dishes available right now</h3>
    <p>Please check back shortly or ask canteen staff to publish today's menu.</p>
  </div>
<?php else: ?>
  <div class="menu-grid">
    <?php foreach ($items as $item): ?>
      <?php
      $searchBlob = strtolower($item['name'] . ' ' . $item['category'] . ' ' . ($item['description'] ?? ''));
      ?>
      <article class="dish-card"
               data-dish-card
               data-category="<?php echo e($item['category']); ?>"
               data-search="<?php echo e($searchBlob); ?>">
        <div class="dish-media">
          <span class="dish-cat"><?php echo e($item['category']); ?></span>
          <?php $imgSrc = media_url($item['image_url'] ?? null); ?>
          <?php if ($imgSrc !== null): ?>
            <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($item['name']); ?>">
          <?php else: ?>
            <span aria-hidden="true"><?php echo category_emoji($item['category']); ?></span>
          <?php endif; ?>
        </div>

        <div class="dish-body">
          <h3 class="dish-title"><?php echo e($item['name']); ?></h3>
          <p class="dish-desc"><?php echo e($item['description'] ?: 'Freshly prepared at the university canteen.'); ?></p>
          <div class="dish-meta">
            <span class="dish-price"><?php echo e(format_currency($item['price'])); ?></span>
            <span class="dish-prep">~<?php echo (int) ($item['prep_minutes'] ?? 10); ?> min prep</span>
          </div>

          <?php if (is_logged_in()): ?>
            <form action="<?php echo e(app_url('actions/cart.php')); ?>" method="POST" class="qty-form">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="item_id" value="<?php echo (int) $item['item_id']; ?>">
              <input type="hidden" name="redirect" value="public/menu.php">

              <div class="qty-stepper" data-qty-stepper>
                <button type="button" data-qty-minus aria-label="Decrease quantity">&minus;</button>
                <input type="number" name="quantity" value="1" min="1" max="50" aria-label="Quantity">
                <button type="button" data-qty-plus aria-label="Increase quantity">+</button>
              </div>

              <button type="submit" class="btn btn-primary" style="flex:1;">Add to Cart</button>
            </form>
          <?php else: ?>
            <a href="<?php echo e(app_url('public/login.php')); ?>" class="btn btn-secondary" style="width:100%;">Login to Order</a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <div id="menuEmptyFilter" class="card empty-state" style="display:none;margin-top:20px;">
    <h3 style="margin-top:0;">No matching menu items</h3>
    <p>Try a different search keyword or category.</p>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
