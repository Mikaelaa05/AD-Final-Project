<?php
declare(strict_types=1);
/**
 * PostgreSQL Database Seeder Utility - All Tables
 * Seeds all database tables with sample data
 */

require_once __DIR__ . '/../bootstrap.php';
require_once BASE_PATH . '/vendor/autoload.php';
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

echo "🌱 **POSTGRESQL DATABASE SEEDER - ALL TABLES**\n";
echo "===============================================\n\n";

$seedOrder = [
    'users' => [
        'description' => 'Team members and admin users',
        'dataFile' => 'users.staticData.php',
        'depends' => []
    ],
    'customers' => [
        'description' => 'Website signups and customer accounts',
        'dataFile' => 'customers.staticData.php', 
        'depends' => []
    ],
    'products' => [
        'description' => 'Product catalog and inventory',
        'dataFile' => 'products.staticData.php',
        'depends' => []
    ],
    'orders' => [
        'description' => 'Customer orders and purchase history',
        'dataFile' => 'orders.staticData.php',
        'depends' => ['customers', 'products']
    ],
    'order_items' => [
        'description' => 'Order line items and product details',
        'dataFile' => 'orders.staticData.php', // Same file, different processing
        'depends' => ['orders', 'products']
    ],
    'projects' => [
        'description' => 'Project management data',
        'dataFile' => 'projects.staticData.php',
        'depends' => []
    ],
    'tasks' => [
        'description' => 'Task assignments and tracking',
        'dataFile' => 'tasks.staticData.php',
        'depends' => ['projects', 'users']
    ],
    'project_users' => [
        'description' => 'Project-user relationships',
        'dataFile' => 'project_users.staticData.php',
        'depends' => ['projects', 'users']
    ]
];

$successCount = 0;
$totalTables = count($seedOrder);
$seededData = [];

foreach ($seedOrder as $table => $config) {
    echo "🌱 **Seeding {$table} table**\n";
    echo "   Purpose: {$config['description']}\n";
    
    try {
        // Check if table exists
        $result = $pdo->query("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = '{$table}'
            )
        ");
        
        if (!$result->fetchColumn()) {
            echo "   ❌ Table doesn't exist. Run migration first.\n";
            echo "   ⏭️  Skipping {$table} table\n\n";
            continue;
        }
        
        // Apply model schema first to ensure table is ready
        $modelPath = DATABASE_PATH . "/{$table}.model.sql";
        if (file_exists($modelPath)) {
            $sql = file_get_contents($modelPath);
            if ($sql !== false && !empty(trim($sql))) {
                $pdo->exec($sql);
            }
        }
        
        // Clear existing data
        echo "   🧹 Clearing existing data...\n";
        try {
            $pdo->exec("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE;");
        } catch (PDOException $e) {
            echo "   💡 Truncate skipped: " . $e->getMessage() . "\n";
        }
        
        // Load data file
        $dataPath = DUMMIES_PATH . '/' . $config['dataFile'];
        
        if (!file_exists($dataPath)) {
            echo "   ⚠️  Data file not found: {$dataPath}\n";
            echo "   ⏭️  Skipping data seeding for {$table}\n\n";
            $successCount++; // Count as success since table exists
            continue;
        }
        
        $data = require $dataPath;
        
        if (empty($data)) {
            echo "   ⚠️  No data found in file\n";
            echo "   ⏭️  Skipping data seeding for {$table}\n\n";
            $successCount++; // Count as success since table exists
            continue;
        }
        
        // Seed based on table type
        $insertedCount = 0;
        
        switch ($table) {
            case 'users':
                $stmt = $pdo->prepare("
                    INSERT INTO users (id, username, email, phone, password, first_name, last_name, role, is_active)
                    VALUES (:id, :username, :email, :phone, :password, :fn, :ln, :role, :is_active)
                ");
                
                foreach ($data as $u) {
                    $uuid = generate_uuid();
                    $email = $u['email'] ?? (strtolower($u['username']) . '@adfinalproject.dev');
                    $phone = $u['phone'] ?? null;
                    
                    $stmt->execute([
                        ':id' => $uuid,
                        ':username' => $u['username'],
                        ':email' => $email,
                        ':phone' => $phone,
                        ':password' => password_hash($u['password'], PASSWORD_DEFAULT),
                        ':fn' => $u['first_name'],
                        ':ln' => $u['last_name'],
                        ':role' => $u['role'],
                        ':is_active' => true,
                    ]);
                    
                    $seededData['users'][$u['username']] = $uuid;
                    $insertedCount++;
                    echo "   👤 {$u['username']}: {$u['first_name']} {$u['last_name']} ({$u['role']})\n";
                }
                break;
                
            case 'customers':
                $stmt = $pdo->prepare("
                    INSERT INTO customers (id, name, email, phone, address) 
                    VALUES (:id, :name, :email, :phone, :address)
                ");
                
                foreach ($data as $customer) {
                    $uuid = generate_uuid();
                    $stmt->execute([
                        ':id' => $uuid,
                        ':name' => $customer['name'],
                        ':email' => $customer['email'],
                        ':phone' => $customer['phone'],
                        ':address' => $customer['address']
                    ]);
                    
                    $seededData['customers'][$customer['email']] = $uuid;
                    $insertedCount++;
                    echo "   👥 {$customer['name']} ({$customer['email']})\n";
                }
                break;
                
            case 'products':
                $stmt = $pdo->prepare("
                    INSERT INTO products (
                        id, name, description, category, price, cost, sku, 
                        stock_quantity, weight, is_active
                    ) VALUES (
                        :id, :name, :description, :category, :price, :cost, :sku, 
                        :stock_quantity, :weight, :is_active
                    )
                ");
                
                foreach ($data as $p) {
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
                    
                    $seededData['products'][$p['sku']] = $uuid;
                    $insertedCount++;
                    echo "   🛍️  {$p['sku']}: {$p['name']} (\${$p['price']})\n";
                }
                break;
                
            case 'orders':
                if (empty($seededData['customers'])) {
                    echo "   ⚠️  Missing dependency data (customers)\n";
                    break;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO orders (
                        id, customer_id, order_number, status, total_amount, subtotal, 
                        tax_amount, shipping_amount, discount_amount, payment_method, 
                        payment_status, shipping_address, billing_address, notes
                    ) VALUES (
                        :id, :customer_id, :order_number, :status, :total_amount, :subtotal,
                        :tax_amount, :shipping_amount, :discount_amount, :payment_method,
                        :payment_status, :shipping_address, :billing_address, :notes
                    )
                ");
                
                foreach ($data as $order) {
                    try {
                        // Find customer by email
                        $customerId = $seededData['customers'][$order['customer_email']] ?? null;
                        
                        if (!$customerId) {
                            echo "   ⚠️  Customer not found: {$order['customer_email']}\n";
                            continue;
                        }
                        
                        $orderId = generate_uuid();
                        $orderNumber = 'ORD-' . date('Ymd') . '-' . sprintf('%04d', $insertedCount + 1);
                        
                        $stmt->execute([
                            ':id' => $orderId,
                            ':customer_id' => $customerId,
                            ':order_number' => $orderNumber,
                            ':status' => $order['status'],
                            ':total_amount' => $order['total_amount'],
                            ':subtotal' => $order['subtotal'],
                            ':tax_amount' => $order['tax_amount'],
                            ':shipping_amount' => $order['shipping_amount'],
                            ':discount_amount' => $order['discount_amount'],
                            ':payment_method' => $order['payment_method'],
                            ':payment_status' => $order['payment_status'],
                            ':shipping_address' => $order['shipping_address'],
                            ':billing_address' => $order['billing_address'],
                            ':notes' => $order['notes']
                        ]);
                        
                        $seededData['orders'][$orderNumber] = [
                            'id' => $orderId,
                            'items' => $order['items']
                        ];
                        
                        $insertedCount++;
                        echo "   📦 {$orderNumber}: {$order['payment_method']} - \${$order['total_amount']} ({$order['status']})\n";
                        
                    } catch (Exception $e) {
                        echo "   ❌ Failed to create order: " . $e->getMessage() . "\n";
                    }
                }
                break;

            case 'order_items':
                if (empty($seededData['orders']) || empty($seededData['products'])) {
                    echo "   ⚠️  Missing dependency data (orders/products)\n";
                    break;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
                    VALUES (:order_id, :product_id, :quantity, :unit_price, :total_price)
                ");
                
                foreach ($seededData['orders'] as $orderNumber => $orderData) {
                    foreach ($orderData['items'] as $item) {
                        try {
                            // Find product by SKU
                            $productId = $seededData['products'][$item['product_sku']] ?? null;
                            
                            if (!$productId) {
                                echo "   ⚠️  Product not found: {$item['product_sku']}\n";
                                continue;
                            }
                            
                            $totalPrice = $item['quantity'] * $item['unit_price'];
                            
                            $stmt->execute([
                                ':order_id' => $orderData['id'],
                                ':product_id' => $productId,
                                ':quantity' => $item['quantity'],
                                ':unit_price' => $item['unit_price'],
                                ':total_price' => $totalPrice
                            ]);
                            
                            $insertedCount++;
                            echo "   🛒 {$orderNumber}: {$item['product_sku']} x{$item['quantity']} (\${$totalPrice})\n";
                            
                        } catch (Exception $e) {
                            echo "   ❌ Failed to create order item: " . $e->getMessage() . "\n";
                        }
                    }
                }
                break;
                
            case 'projects':
                $stmt = $pdo->prepare("
                    INSERT INTO projects (id, name, description)
                    VALUES (:id, :name, :description)
                ");
                
                foreach ($data as $project) {
                    $uuid = generate_uuid();
                    $stmt->execute([
                        ':id' => $uuid,
                        ':name' => $project['name'],
                        ':description' => $project['description']
                    ]);
                    
                    $seededData['projects'][$project['name']] = $uuid;
                    $insertedCount++;
                    echo "   📁 {$project['name']}\n";
                }
                break;
                
            case 'tasks':
                // Tasks depend on projects and users
                if (empty($seededData['projects']) || empty($seededData['users'])) {
                    echo "   ⚠️  Missing dependency data (projects/users)\n";
                    break;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO tasks (project_id, assigned_to, title, description, status, due_date)
                    VALUES (:project_id, :assigned_to, :title, :description, :status, :due_date)
                ");
                
                foreach ($data as $task) {
                    // Skip if dependencies not available
                    if ($task['project_id'] === null || $task['assigned_to'] === null) {
                        continue;
                    }
                    
                    $stmt->execute([
                        ':project_id' => array_values($seededData['projects'])[0], // Use first project
                        ':assigned_to' => array_values($seededData['users'])[0], // Use first user
                        ':title' => $task['title'],
                        ':description' => $task['description'],
                        ':status' => $task['status'],
                        ':due_date' => $task['due_date']
                    ]);
                    
                    $insertedCount++;
                    echo "   📋 {$task['title']}\n";
                }
                break;
                
            case 'project_users':
                // Project users depend on projects and users
                if (empty($seededData['projects']) || empty($seededData['users'])) {
                    echo "   ⚠️  Missing dependency data (projects/users)\n";
                    break;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO project_users (project_id, user_id)
                    VALUES (:project_id, :user_id)
                ");
                
                // Assign all users to all projects
                foreach ($seededData['projects'] as $projectId) {
                    foreach ($seededData['users'] as $userId) {
                        $stmt->execute([
                            ':project_id' => $projectId,
                            ':user_id' => $userId
                        ]);
                        $insertedCount++;
                    }
                }
                echo "   🔗 {$insertedCount} project-user relationships created\n";
                break;
                
            default:
                echo "   ⚠️  No seeding logic for {$table} table\n";
                break;
        }
        
        echo "   ✅ Successfully seeded {$insertedCount} records\n";
        $successCount++;
        
    } catch (PDOException $e) {
        echo "   ❌ Seeding failed: " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "🎉 **SEEDING SUMMARY**\n";
echo "======================\n";
echo "✅ Successfully seeded: {$successCount}/{$totalTables} tables\n\n";

// Show final statistics
echo "📊 **DATABASE STATISTICS**\n";
echo "===========================\n";

foreach (array_keys($seedOrder) as $table) {
    try {
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
            echo "📋 {$table}: {$count} records\n";
        } else {
            echo "❌ {$table}: Table not found\n";
        }
    } catch (Exception $e) {
        echo "❌ {$table}: Error checking - {$e->getMessage()}\n";
    }
}

// Show order-specific statistics
echo "\n💰 **ORDERS STATISTICS**\n";
echo "=========================\n";
try {
    $result = $pdo->query("
        SELECT 
            status,
            COUNT(*) as order_count,
            SUM(total_amount) as total_revenue
        FROM orders 
        GROUP BY status
        ORDER BY status
    ");
    
    $totalRevenue = 0;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "📦 {$row['status']}: {$row['order_count']} orders (\${$row['total_revenue']})\n";
        if ($row['status'] !== 'cancelled') {
            $totalRevenue += $row['total_revenue'];
        }
    }
    echo "💰 Total Revenue: \${$totalRevenue}\n";
    
} catch (Exception $e) {
    echo "❌ Could not calculate order statistics\n";
}

if ($successCount === $totalTables) {
    echo "\n🎯 All tables seeded successfully!\n";
    echo "💡 Your database is ready for development!\n";
    echo "🛒 Orders system is fully functional!\n";
    exit(0);
} else {
    echo "\n⚠️  Some tables failed to seed. Check the errors above.\n";
    exit(1);
}
?>