<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'bootstrap.php';
require_once UTILS_PATH . '/envSetter.util.php';

$host = $typeConfig['pgHost'];
$port = $typeConfig['pgPort'];
$username = $typeConfig['pgUser'];
$password = $typeConfig['pgPass'];
$dbname = $typeConfig['pgDb'];

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "📦 **PRODUCTS DATABASE VERIFICATION**\n";
echo "=====================================\n\n";

// Get product count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
$total = $stmt->fetchColumn();
echo "Total Products in Database: {$total}\n\n";

// Get products by category
$stmt = $pdo->query("
    SELECT category, COUNT(*) as count, 
           MIN(price) as min_price, 
           MAX(price) as max_price,
           SUM(stock_quantity) as total_stock
    FROM products 
    GROUP BY category 
    ORDER BY category
");

echo "📊 **PRODUCTS BY CATEGORY:**\n";
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($categories as $cat) {
    echo "• {$cat['category']}: {$cat['count']} items\n";
    echo "  Price Range: \${$cat['min_price']} - \${$cat['max_price']}\n";
    echo "  Total Stock: {$cat['total_stock']} units\n\n";
}

// Get all products
echo "🛍️ **COMPLETE PRODUCT CATALOG:**\n";
$stmt = $pdo->query("
    SELECT name, category, price, stock_quantity, sku 
    FROM products 
    ORDER BY category, name
");

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($products as $p) {
    echo "• {$p['name']}\n";
    echo "  Category: {$p['category']}\n";
    echo "  Price: \${$p['price']}\n";
    echo "  Stock: {$p['stock_quantity']} units\n";
    echo "  SKU: {$p['sku']}\n\n";
}

echo "✅ **Products Database Verification Complete!**\n";
