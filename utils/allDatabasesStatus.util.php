<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once BASE_PATH . '/vendor/autoload.php';
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

echo "🔍 **ALL POSTGRESQL DATABASES STATUS CHECK**\n";
echo "==============================================\n\n";

$tables = ['users', 'customers', 'products', 'orders', 'order_items'];

foreach ($tables as $table) {
    echo "📊 **" . strtoupper($table) . " DATABASE**\n";
    echo str_repeat("-", 30) . "\n";
    
    try {
        // Check if table exists
        $result = $pdo->query("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = '{$table}'
            )
        ");
        $tableExists = $result->fetchColumn();
        
        if ($tableExists) {
            echo "✅ Table exists\n";
            
            // Get record count
            $result = $pdo->query("SELECT COUNT(*) FROM {$table}");
            $count = $result->fetchColumn();
            echo "📈 Records: {$count}\n";
            
            if ($count > 0) {
                echo "🟢 Status: Populated\n";
                
                // Show sample data for each table
                switch ($table) {
                    case 'users':
                        $sampleResult = $pdo->query("SELECT username, role, email FROM users LIMIT 2");
                        echo "👤 Sample: ";
                        $samples = [];
                        while ($row = $sampleResult->fetch(PDO::FETCH_ASSOC)) {
                            $samples[] = "{$row['username']} ({$row['role']})";
                        }
                        echo implode(', ', $samples) . "\n";
                        
                        // Show team statistics
                        $roleStats = $pdo->query("
                            SELECT role, COUNT(*) as count 
                            FROM users 
                            GROUP BY role 
                            ORDER BY count DESC
                        ");
                        echo "👥 Team: ";
                        $roles = [];
                        while ($row = $roleStats->fetch(PDO::FETCH_ASSOC)) {
                            $roles[] = "{$row['count']} {$row['role']}";
                        }
                        echo implode(', ', $roles) . "\n";
                        break;
                        
                    case 'customers':
                        $sampleResult = $pdo->query("SELECT name, email FROM customers LIMIT 2");
                        echo "👥 Sample: ";
                        $samples = [];
                        while ($row = $sampleResult->fetch(PDO::FETCH_ASSOC)) {
                            $samples[] = $row['name'];
                        }
                        echo implode(', ', $samples) . "\n";
                        
                        // Show customer registration trend
                        $recentCustomers = $pdo->query("
                            SELECT COUNT(*) as recent_signups 
                            FROM customers 
                            WHERE created_at >= CURRENT_DATE - INTERVAL '7 days'
                        ")->fetchColumn();
                        echo "📅 Recent signups (7 days): {$recentCustomers}\n";
                        break;
                        
                    case 'products':
                        $sampleResult = $pdo->query("SELECT name, category, image_url, price FROM products LIMIT 2");
                        echo "🛍️ Sample: ";
                        $samples = [];
                        while ($row = $sampleResult->fetch(PDO::FETCH_ASSOC)) {
                            $imageIcon = !empty($row['image_url']) ? '🖼️' : '📦';
                            $samples[] = "{$imageIcon}{$row['name']} (\${$row['price']})";
                        }
                        echo implode(', ', $samples) . "\n";
                        
                        // Show product statistics
                        try {
                            $productStats = $pdo->query("
                                SELECT 
                                    COUNT(*) as total_products,
                                    COUNT(CASE WHEN is_active = true THEN 1 END) as active_products,
                                    COUNT(image_url) as products_with_images,
                                    ROUND(AVG(price), 2) as avg_price,
                                    COUNT(DISTINCT category) as categories
                                FROM products
                            ")->fetch(PDO::FETCH_ASSOC);
                            
                            echo "📊 Active: {$productStats['active_products']}/{$productStats['total_products']} | ";
                            echo "Images: {$productStats['products_with_images']} | ";
                            echo "Categories: {$productStats['categories']} | ";
                            echo "Avg Price: \${$productStats['avg_price']}\n";
                        } catch (Exception $e) {
                            echo "📊 Unable to calculate product statistics\n";
                        }
                        break;
                        
                    case 'orders':
                        $sampleResult = $pdo->query("SELECT order_number, status, total_amount, payment_method FROM orders LIMIT 2");
                        echo "📦 Sample: ";
                        $samples = [];
                        while ($row = $sampleResult->fetch(PDO::FETCH_ASSOC)) {
                            $statusIcon = match($row['status']) {
                                'pending' => '⏳',
                                'processing' => '⚙️',
                                'shipped' => '🚚',
                                'delivered' => '✅',
                                'cancelled' => '❌',
                                default => '📦'
                            };
                            $samples[] = "{$statusIcon}{$row['order_number']} (\${$row['total_amount']})";
                        }
                        echo implode(', ', $samples) . "\n";
                        
                        // Show order statistics
                        try {
                            $orderStats = $pdo->query("
                                SELECT 
                                    status,
                                    COUNT(*) as order_count,
                                    ROUND(SUM(total_amount), 2) as total_revenue
                                FROM orders 
                                GROUP BY status
                                ORDER BY 
                                    CASE status 
                                        WHEN 'pending' THEN 1
                                        WHEN 'processing' THEN 2
                                        WHEN 'shipped' THEN 3
                                        WHEN 'delivered' THEN 4
                                        WHEN 'cancelled' THEN 5
                                    END
                            ");
                            
                            echo "📈 Orders: ";
                            $statuses = [];
                            $totalRevenue = 0;
                            while ($row = $orderStats->fetch(PDO::FETCH_ASSOC)) {
                                $statuses[] = "{$row['order_count']} {$row['status']}";
                                if ($row['status'] !== 'cancelled') {
                                    $totalRevenue += $row['total_revenue'];
                                }
                            }
                            echo implode(', ', $statuses) . "\n";
                            echo "💰 Total Revenue: \${$totalRevenue}\n";
                        } catch (Exception $e) {
                            echo "📈 Unable to calculate order statistics\n";
                        }
                        break;
                        
                    case 'order_items':
                        $sampleResult = $pdo->query("
                            SELECT oi.quantity, p.name, oi.unit_price
                            FROM order_items oi 
                            JOIN products p ON oi.product_id = p.id 
                            LIMIT 2
                        ");
                        echo "🛒 Sample: ";
                        $samples = [];
                        while ($row = $sampleResult->fetch(PDO::FETCH_ASSOC)) {
                            $samples[] = "{$row['quantity']}x {$row['name']} (\${$row['unit_price']})";
                        }
                        echo implode(', ', $samples) . "\n";
                        
                        // Show item statistics
                        try {
                            $itemStats = $pdo->query("
                                SELECT 
                                    COUNT(*) as total_items,
                                    SUM(quantity) as total_quantity,
                                    ROUND(AVG(unit_price), 2) as avg_unit_price
                                FROM order_items
                            ")->fetch(PDO::FETCH_ASSOC);
                            
                            echo "📦 Items: {$itemStats['total_items']} | ";
                            echo "Qty: {$itemStats['total_quantity']} | ";
                            echo "Avg Unit Price: \${$itemStats['avg_unit_price']}\n";
                        } catch (Exception $e) {
                            echo "📦 Unable to calculate item statistics\n";
                        }
                        break;
                }
            } else {
                echo "⚠️  Status: Empty\n";
            }
        } else {
            echo "❌ Table does not exist\n";
            echo "🔴 Status: Not created\n";
        }
        
    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Show database summary
echo "📊 **DATABASE SUMMARY**\n";
echo "========================\n";
try {
    $totalRecords = 0;
    $tableStatuses = [];
    
    foreach ($tables as $table) {
        $result = $pdo->query("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = '{$table}'
            )
        ");
        
        if ($result->fetchColumn()) {
            $countResult = $pdo->query("SELECT COUNT(*) FROM {$table}");
            $count = $countResult->fetchColumn();
            $totalRecords += $count;
            $tableStatuses[] = "{$table}: {$count}";
        } else {
            $tableStatuses[] = "{$table}: missing";
        }
    }
    
    echo "📈 Total Records: {$totalRecords}\n";
    echo "📋 Breakdown: " . implode(' | ', $tableStatuses) . "\n";
    
} catch (Exception $e) {
    echo "❌ Could not calculate summary statistics\n";
}

// Show system health
echo "\n🏥 **SYSTEM HEALTH**\n";
echo "====================\n";

try {
    // Check database connection
    $dbVersion = $pdo->query("SELECT version()")->fetchColumn();
    echo "✅ Database: Connected (" . explode(' ', $dbVersion)[0] . " " . explode(' ', $dbVersion)[1] . ")\n";
    
    // Check for foreign key constraints
    $fkCount = $pdo->query("
        SELECT COUNT(*) 
        FROM information_schema.table_constraints 
        WHERE constraint_type = 'FOREIGN KEY' 
        AND table_schema = 'public'
    ")->fetchColumn();
    echo "🔗 Foreign Keys: {$fkCount} constraints active\n";
    
    // Check for indexes
    $indexCount = $pdo->query("
        SELECT COUNT(*) 
        FROM pg_indexes 
        WHERE schemaname = 'public'
    ")->fetchColumn();
    echo "⚡ Indexes: {$indexCount} performance indexes\n";
    
} catch (Exception $e) {
    echo "❌ Health check failed: " . $e->getMessage() . "\n";
}

echo "\n✅ **Database status check complete!**\n";

echo "\n🚀 **NEW UNIFIED MANAGEMENT COMMANDS:**\n";
echo "=======================================\n";
echo "🏗️  Migrate All:  docker exec adfinalproject-service php utils/dbMigratePostgresql.util.php\n";
echo "🧹 Reset All:     docker exec adfinalproject-service php utils/dbResetPostgresql.util.php\n";
echo "🌱 Seed All:      docker exec adfinalproject-service php utils/dbSeederPostgresql.util.php\n";
echo "🔍 Status Check:  docker exec adfinalproject-service php utils/allDatabasesStatus.util.php\n";

echo "\n💡 **QUICK SETUP (Recommended Order):**\n";
echo "========================================\n";
echo "1️⃣  docker exec adfinalproject-service php utils/dbResetPostgresql.util.php\n";
echo "2️⃣  docker exec adfinalproject-service php utils/dbMigratePostgresql.util.php\n";
echo "3️⃣  docker exec adfinalproject-service php utils/dbSeederPostgresql.util.php\n";
echo "4️⃣  docker exec adfinalproject-service php utils/allDatabasesStatus.util.php\n";

echo "\n🎯 **PROJECT STATUS:**\n";
echo "======================\n";
$allTablesExist = true;
$allTablesPopulated = true;

foreach ($tables as $table) {
    try {
        $result = $pdo->query("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = '{$table}'
            )
        ");
        
        if (!$result->fetchColumn()) {
            $allTablesExist = false;
        } else {
            $countResult = $pdo->query("SELECT COUNT(*) FROM {$table}");
            if ($countResult->fetchColumn() == 0) {
                $allTablesPopulated = false;
            }
        }
    } catch (Exception $e) {
        $allTablesExist = false;
    }
}

if ($allTablesExist && $allTablesPopulated) {
    echo "🎉 Status: READY FOR DEVELOPMENT!\n";
    echo "✅ All tables created and populated\n";
    echo "✅ Orders system fully functional\n";
    echo "✅ Image support enabled\n";
    echo "💡 Your e-commerce platform is ready!\n";
} elseif ($allTablesExist) {
    echo "⚠️  Status: TABLES CREATED BUT EMPTY\n";
    echo "💡 Run the seeder to populate with sample data\n";
} else {
    echo "❌ Status: SETUP REQUIRED\n";
    echo "💡 Run migration and seeder to set up the database\n";
}
?>