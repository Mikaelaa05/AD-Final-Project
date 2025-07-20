<?php
<?php
declare(strict_types=1);

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

echo "🌱 **POSTGRESQL DATABASE SEEDER - CORE TABLES**\n";
echo "================================================\n\n";

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
                        stock_quantity, weight, is_active, image_url, image_alt_text, image_caption
                    ) VALUES (
                        :id, :name, :description, :category, :price, :cost, :sku, 
                        :stock_quantity, :weight, :is_active, :image_url, :image_alt_text, :image_caption
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
                        ':image_url' => $p['image_url'] ?? null,
                        ':image_alt_text' => $p['image_alt_text'] ?? null,
                        ':image_caption' => $p['image_caption'] ?? null
                    ]);
                    
                    $seededData['products'][$p['sku']] = $uuid;
                    $insertedCount++;
                    $imageStatus = !empty($p['image_url']) ? '🖼️' : '📦';
                    echo "   {$imageStatus} {$p['sku']}: {$p['name']} (\${$p['price']})\n";
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

if ($successCount === $totalTables) {
    echo "\n🎯 All tables seeded successfully!\n";
    echo "💡 Your database is ready for development!\n";
    echo "🔐 Default login credentials:\n";
    echo "   Username: admin\n";
    echo "   Password: password\n";
    echo "\n📝 Note: Orders functionality will be created through the application\n";
    exit(0);
} else {
    echo "\n⚠️  Some tables failed to seed. Check the errors above.\n";
    exit(1);
}
?>