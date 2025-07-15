<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'bootstrap.php';
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

echo "🛍️ Testing Products Database...\n\n";

// Test products table
$products = require DUMMIES_PATH . '/products.staticData.php';
echo "Seeding products…\n";
$stmt = $pdo->prepare("
    INSERT INTO products (id, name, description, category, price, stock_quantity, sku, is_active)
    VALUES (:id, :name, :description, :category, :price, :stock_quantity, :sku, :is_active)
");

foreach ($products as $p) {
    $uuid = generate_uuid();
    try {
        $stmt->execute([
            ':id' => $uuid,
            ':name' => $p['name'],
            ':description' => $p['description'],
            ':category' => $p['category'],
            ':price' => $p['price'],
            ':stock_quantity' => $p['stock_quantity'],
            ':sku' => $p['sku'],
            ':is_active' => $p['is_active'] ? 'true' : 'false'
        ]);
        echo "✅ Added: {$p['name']}\n";
    } catch (PDOException $e) {
        echo "❌ Error adding {$p['name']}: " . $e->getMessage() . "\n";
    }
}

// Query and display products
echo "\n📦 Products in database:\n";
$stmt = $pdo->query("SELECT name, category, price, stock_quantity, sku FROM products ORDER BY category, name");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $product) {
    echo "- {$product['name']} ({$product['category']}) - \${$product['price']} - Stock: {$product['stock_quantity']} - SKU: {$product['sku']}\n";
}

echo "\n✅ Products database test complete!\n";
