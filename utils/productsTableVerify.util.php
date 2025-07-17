<?php
declare(strict_types=1);

require_once BASE_PATH . '/vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';
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

echo "🔍 Verifying products database…\n";

try {
    // Check if table exists
    $result = $pdo->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'products'
        )
    ");
    $tableExists = $result->fetchColumn();
    
    if (!$tableExists) {
        echo "❌ Products table does not exist!\n";
        exit(1);
    }
    
    echo "✅ Products table exists\n";
    
    // Get total product count
    $result = $pdo->query("SELECT COUNT(*) FROM products");
    $totalProducts = $result->fetchColumn();
    echo "📊 Total products: {$totalProducts}\n";
    
    if ($totalProducts > 0) {
        // Product category breakdown
        echo "\n📈 Product Category Breakdown:\n";
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
            echo "  - {$row['category']}: {$row['count']} products\n";
            echo "    Avg Price: \${$row['avg_price']}\n";
            echo "    Total Stock: {$row['total_stock']}\n\n";
        }
        
        // Active vs Inactive
        echo "🟢 Active/Inactive Status:\n";
        $result = $pdo->query("
            SELECT 
                is_active,
                COUNT(*) as count
            FROM products 
            GROUP BY is_active
        ");
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['is_active'] ? 'Active' : 'Inactive';
            echo "  - {$status}: {$row['count']} products\n";
        }
        
        // Top products by price
        echo "\n💰 Top 3 Most Expensive Products:\n";
        $result = $pdo->query("
            SELECT 
                name,
                sku,
                price,
                category,
                stock_quantity
            FROM products 
            ORDER BY price DESC 
            LIMIT 3
        ");
        
        $rank = 1;
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$rank}. {$row['name']} ({$row['sku']})\n";
            echo "     Price: \${$row['price']}\n";
            echo "     Category: {$row['category']}\n";
            echo "     Stock: {$row['stock_quantity']}\n\n";
            $rank++;
        }
        
        // Sample product data
        echo "📝 Sample Product Records:\n";
        $result = $pdo->query("
            SELECT 
                name,
                sku,
                category,
                price,
                stock_quantity
            FROM products 
            LIMIT 3
        ");
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  • {$row['sku']}: {$row['name']} ({$row['category']})\n";
            echo "    💰 \${$row['price']}\n";
            echo "    📦 Stock: {$row['stock_quantity']}\n\n";
        }
    } else {
        echo "⚠️  No products found in database\n";
    }
    
    echo "✅ Products database verification complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
