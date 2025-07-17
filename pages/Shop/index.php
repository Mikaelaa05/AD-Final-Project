<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../bootstrap.php';
require_once UTILS_PATH . '/envSetter.util.php';

session_start();

if (!isset($_SESSION['user'])) {
    include ERRORS_PATH . '/unauthorized.error.php';
    exit;
}

// Database connection
$host = $typeConfig['pgHost'];
$port = $typeConfig['pgPort'];
$username = $typeConfig['pgUser'];
$password = $typeConfig['pgPass'];
$dbname = $typeConfig['pgDb'];

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// Get filter parameters
$categoryFilter = $_GET['category'] ?? '';

// Build query
$sql = "SELECT * FROM products WHERE is_active = true";
$params = [];

if (!empty($categoryFilter)) {
    $sql .= " AND category = :category";
    $params['category'] = $categoryFilter;
}

$sql .= " ORDER BY name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for filter dropdown
$categoriesStmt = $pdo->query("SELECT DISTINCT category FROM products WHERE is_active = true ORDER BY category");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Define the content for the layout
ob_start();
?>
<div class="shop-header">
    <h1>Product Shop</h1>
    <p>Browse our selection of high-tech products and cybernetic enhancements</p>
</div>

<?php if (!empty($categories)): ?>
    <div class="shop-filters">
        <form method="GET" class="filter-group">
            <label for="category">Filter by Category:</label>
            <select name="category" id="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>" <?= $categoryFilter === $category ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($categoryFilter)): ?>
                <a href="?" class="btn btn-secondary">Clear Filter</a>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

<?php if (!empty($products)): ?>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>

                <?php if (!empty($product['category'])): ?>
                    <span class="product-category"><?= htmlspecialchars($product['category']) ?></span>
                <?php endif; ?>

                <?php if (!empty($product['description'])): ?>
                    <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                <?php endif; ?>

                <div class="product-details">
                    <?php if (!empty($product['sku'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">SKU:</span>
                            <span><?= htmlspecialchars($product['sku']) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['weight'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Weight:</span>
                            <span><?= htmlspecialchars($product['weight']) ?>kg</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="product-price">$<?= number_format($product['price'], 2) ?></div>

                <?php
                $stockQuantity = (int) $product['stock_quantity'];
                if ($stockQuantity > 20):
                    $stockClass = 'in-stock';
                    $stockText = 'In Stock (' . $stockQuantity . ' available)';
                elseif ($stockQuantity > 0):
                    $stockClass = 'low-stock';
                    $stockText = 'Low Stock (' . $stockQuantity . ' remaining)';
                else:
                    $stockClass = 'out-of-stock';
                    $stockText = 'Out of Stock';
                endif;
                ?>

                <div class="product-stock <?= $stockClass ?>">
                    <?= $stockText ?>
                </div>

                <div class="product-actions">
                    <?php if ($stockQuantity > 0): ?>
                        <button class="btn btn-primary">Add to Cart</button>
                        <button class="btn btn-secondary">View Details</button>
                    <?php else: ?>
                        <button class="btn btn-primary" disabled>Out of Stock</button>
                        <button class="btn btn-secondary">Notify When Available</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-products">
        <h3>No Products Found</h3>
        <p>
            <?php if (!empty($categoryFilter)): ?>
                No products found in the "<?= htmlspecialchars($categoryFilter) ?>" category.
                <br><a href="?" class="btn btn-primary">View All Products</a>
            <?php else: ?>
                No products are currently available. Please check back later.
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Shop - Product Catalog</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/shop.css">
</head>

<body>
    <?php include LAYOUTS_PATH . '/main.layout.php'; ?>
</body>

</html>