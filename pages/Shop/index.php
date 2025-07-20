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

// Get all active products
$sql = "SELECT * FROM products WHERE is_active = true ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Image mapping for products
$productImages = [
    'IONFUEL VIALS' => 'IonFuelVials.png',
    'IONPULSE BYTE' => 'IonpulseByte.png', 
    'IONPULSE SPINE MK. VI' => 'IonpulseSpineMkV1.png',
    'ENERGY PACKS' => 'EnergyPacks.png',
    'NEUROPLASTICITY' => 'Neuroplasticity.png',
    'NEUROSPARK NODE' => 'NeuroSparkNode.png',
    'OVERDRIVE CAPSULE' => 'OverDriveCapsule.png',
    'SYNTHCELL BATTERY PACK' => 'SynthCellBatteryPack.png',
    'SYNTHLUNG UPGRADE' => 'SynthlungUpgrade.png',
    'TESLA NODE' => 'TeslaNode.png'
];

// Define the content for the layout
ob_start();
?>
<div class="shop-container">
<div class="shop-header">
    <h1><span style="color: #ff0040;">SIN</span>THESIZE Marketplace</h1>
    <p>Cybernetic Enhancements • Digital Weaponry • Reality Hacking Tools</p>
</div>

<?php if (!empty($products)): ?>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <?php 
                $productNameUpper = strtoupper($product['name']);
                if (isset($productImages[$productNameUpper])): 
                ?>
                    <div class="product-image">
                        <img src="/pages/Shop/assets/img/<?= $productImages[$productNameUpper] ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             loading="lazy">
                    </div>
                <?php endif; ?>
                
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
                    <?php else: ?>
                        <button class="btn btn-primary" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-products">
        <h3>No Products Found</h3>
        <p>No products are currently available. Please check back later.</p>
    </div>
<?php endif; ?>
</div>

<?php
$content = ob_get_clean();
?>
<!DOCTYPE html>
<html>

<head>
    <title>SINTHESIZE Marketplace - Cybernetic Catalog</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/shop.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include LAYOUTS_PATH . '/main.layout.php'; ?>
</body>

</html>