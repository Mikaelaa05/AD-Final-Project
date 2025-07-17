<?php
declare(strict_types=1);

require_once BASE_PATH . '/vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';
require_once UTILS_PATH . '/envSetter.util.php';

// Helper to generate UUID v4
function generate_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

$host = $typeConfig['pgHost'];
$port = $typeConfig['pgPort'];
$username = $typeConfig['pgUser'];
$password = $typeConfig['pgPass'];
$dbname = $typeConfig['pgDb'];

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "🌱 Seeding products table…\n";

// Create table if not exists
$sql = file_get_contents('database/products.model.sql');
if ($sql === false) {
    throw new RuntimeException("❌ Could not read products.model.sql");
}
$pdo->exec($sql);

// Clear existing data
try {
    $pdo->exec("TRUNCATE TABLE products RESTART IDENTITY CASCADE;");
} catch (PDOException $e) {
    echo "Warning: Could not truncate products table: " . $e->getMessage() . "\n";
}

// Seed products data
$products = require DUMMIES_PATH . '/products.staticData.php';
$stmt = $pdo->prepare("
    INSERT INTO products (
        id, name, description, category, price, cost, sku, 
        stock_quantity, weight, is_active
    ) VALUES (
        :id, :name, :description, :category, :price, :cost, :sku, 
        :stock_quantity, :weight, :is_active
    )
");

$productCount = 0;
foreach ($products as $p) {
    $uuid = generate_uuid();
    $stmt->execute([
        ':id' => $uuid,
        ':name' => $p['name'],
        ':description' => $p['description'],
        ':category' => $p['category'],
        ':price' => $p['price'],
        ':cost' => $p['cost'],
        ':sku' => $p['sku'],
        ':stock_quantity' => $p['stock_quantity'],
        ':weight' => $p['weight'],
        ':is_active' => $p['is_active'] ? 'true' : 'false',
    ]);
    $productCount++;
}

echo "✅ Successfully seeded {$productCount} products!\n";

// Display seeded data summary
echo "\n📊 Products Database Summary:\n";
$result = $pdo->query("
    SELECT 
        category,
        COUNT(*) as count,
        ROUND(AVG(price), 2) as avg_price,
        SUM(stock_quantity) as total_stock
    FROM products 
    GROUP BY category
    ORDER BY count DESC
");

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$row['category']}: {$row['count']} products, Avg Price: \${$row['avg_price']}, Stock: {$row['total_stock']}\n";
}
