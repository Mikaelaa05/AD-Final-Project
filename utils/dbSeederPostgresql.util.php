<?php
declare(strict_types=1);
/**
 * PostgreSQL Database Seeder Utility - Auto-detect Tables
 * Seeds available database tables with sample data
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

echo "🌱 **POSTGRESQL DATABASE SEEDER - AUTO-DETECT**\n";
echo "================================================\n\n";

// Fix the path to point to staticData/dummies
$dummiesPath = BASE_PATH . '/staticData/dummies';
$dataFiles = glob($dummiesPath . '/*.staticData.php');

if (empty($dataFiles)) {
    echo "❌ No data files found in: {$dummiesPath}\n";
    echo "💡 Please create .staticData.php files for your tables\n";
    
    // Try alternative paths for debugging
    $altPaths = [
        BASE_PATH . '/dummies',
        BASE_PATH . '/staticData',
        BASE_PATH . '/data'
    ];
    
    echo "\n🔍 **DEBUGGING - Checking alternative paths:**\n";
    foreach ($altPaths as $altPath) {
        if (is_dir($altPath)) {
            $altFiles = glob($altPath . '/*.staticData.php');
            echo "✅ Found directory: {$altPath} (" . count($altFiles) . " files)\n";
            if (!empty($altFiles)) {
                echo "   Files: " . implode(', ', array_map('basename', $altFiles)) . "\n";
            }
        } else {
            echo "❌ Directory not found: {$altPath}\n";
        }
    }
    exit(1);
}

$availableTables = [];
foreach ($dataFiles as $filePath) {
    $filename = basename($filePath);
    $tableName = str_replace('.staticData.php', '', $filename);
    $availableTables[] = $tableName;
}

// Define seeding order based on dependencies
$seedingOrder = [
    'users',
    'customers', 
    'products',
    'projects',
    'orders',
    'order_items',
    'tasks',
    'project_users'
];

// Filter to only include tables we have data for
$tables = array_intersect($seedingOrder, $availableTables);

$successCount = 0;
$totalTables = count($tables);
$seededData = [];

echo "📝 **SEEDING PLAN**\n";
echo "==================\n";
echo "📁 Dummies path: {$dummiesPath}\n";
echo "📊 Found {$totalTables} data files: " . implode(', ', $tables) . "\n";
echo "🔄 Seeding order (respects dependencies): " . implode(' → ', $tables) . "\n\n";

foreach ($tables as $table) {
    echo "🌱 **Seeding {$table} table**\n";
    
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
        
        // Clear existing data
        echo "   🧹 Clearing existing data...\n";
        try {
            $pdo->exec("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE;");
        } catch (PDOException $e) {
            echo "   💡 Truncate skipped: " . $e->getMessage() . "\n";
        }
        
        // Load data file
        $dataPath = $dummiesPath . "/{$table}.staticData.php";
        
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
                        stock_quantity, weight, is_active, image_url, image_alt_text, image_caption
                    ) VALUES (
                        :id, :name, :description, :category, :price, :cost, :sku, 
                        :stock_quantity, :weight, :is_active, :image_url, :image_alt_text, :image_caption
                    )
                ");
                
                $productCount = 0;
                foreach ($data as $p) {
                    $uuid = generate_uuid();
                    $productCount++;
                    
                    // First 10 products use CSS-based images (NULL image_url)
                    // New products (11+) can use web URLs from the data file
                    $useImageUrl = ($productCount > 10) ? ($p['image_url'] ?? null) : null;
                    
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
                        ':image_url' => $useImageUrl,
                        ':image_alt_text' => $p['image_alt_text'] ?? null,
                        ':image_caption' => $p['image_caption'] ?? null
                    ]);
                    
                    $seededData['products'][$p['sku']] = $uuid;
                    $insertedCount++;
                    
                    // Show appropriate icon based on image type
                    if ($productCount <= 10) {
                        $imageStatus = '🎨'; // CSS-based
                        $imageNote = '(CSS-based)';
                    } else {
                        $imageStatus = !empty($useImageUrl) ? '🖼️' : '📦';
                        $imageNote = !empty($useImageUrl) ? '(Web URL)' : '';
                    }
                    
                    echo "   {$imageStatus} {$p['sku']}: {$p['name']} (\${$p['price']}) {$imageNote}\n";
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
                
            // Add other table cases as needed...
                
            default:
                echo "   ⚠️  No seeding logic for {$table} table\n";
                echo "   💡 Add seeding logic to dbSeederPostgresql.util.php\n";
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

foreach ($tables as $table) {
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

if ($successCount === $totalTables) {
    echo "\n🎯 All available tables seeded successfully!\n";
    echo "💡 Your database is ready for development!\n";
    exit(0);
} else {
    echo "\n⚠️  Some tables failed to seed. Check the errors above.\n";
    exit(1);
}
?>