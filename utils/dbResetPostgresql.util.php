<?php
declare(strict_types=1);
/**
 * PostgreSQL Database Reset Utility - Core Tables Only
 * Drops and recreates core database tables (no orders)
 */

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

echo "🧹 **POSTGRESQL DATABASE RESET - CORE TABLES**\n";
echo "===============================================\n\n";

$tables = [
    'users' => 'Team members and admin users',
    'customers' => 'Website signups and customer accounts', 
    'products' => 'Product catalog and inventory',
    'projects' => 'Project management data',
    'tasks' => 'Task assignments and tracking',
    'project_users' => 'Project-user relationships'
];

$successCount = 0;
$totalTables = count($tables);

echo "⚠️  WARNING: This will completely reset all core PostgreSQL tables!\n";
echo "📊 Tables to reset: " . implode(', ', array_keys($tables)) . "\n";
echo "📝 Note: Orders tables are excluded and will be created dynamically\n\n";

foreach ($tables as $table => $description) {
    echo "🔄 **Resetting {$table} table**\n";
    echo "   Purpose: {$description}\n";
    
    try {
        // Check if table exists first
        $result = $pdo->query("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = '{$table}'
            )
        ");
        
        $tableExists = $result->fetchColumn();
        
        if ($tableExists) {
            // Get current record count
            $countResult = $pdo->query("SELECT COUNT(*) FROM {$table}");
            $currentCount = $countResult->fetchColumn();
            echo "   📊 Current records: {$currentCount}\n";
        } else {
            echo "   📋 Table doesn't exist yet\n";
        }
        
        // Read and apply model file
        $modelPath = DATABASE_PATH . "/{$table}.model.sql";
        
        if (!file_exists($modelPath)) {
            echo "   ❌ Model file not found: {$modelPath}\n";
            echo "   📁 Expected path: {$modelPath}\n";
            echo "   ⏭️  Skipping {$table} table\n\n";
            continue;
        }
        
        $sql = file_get_contents($modelPath);
        if ($sql === false || empty(trim($sql))) {
            echo "   ❌ Model file is empty or unreadable\n";
            echo "   ⏭️  Skipping {$table} table\n\n";
            continue;
        }
        
        // Drop and recreate table
        echo "   🗑️  Dropping existing table...\n";
        $pdo->exec("DROP TABLE IF EXISTS {$table} CASCADE;");
        
        echo "   🏗️  Recreating table from schema...\n";
        $pdo->exec($sql);
        
        // Verify table creation
        $result = $pdo->query("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = '{$table}'
            )
        ");
        
        if ($result->fetchColumn()) {
            echo "   ✅ Table reset successfully\n";
            $successCount++;
        } else {
            echo "   ❌ Table creation verification failed\n";
        }
        
    } catch (PDOException $e) {
        echo "   ❌ Reset failed: " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "🎉 **RESET SUMMARY**\n";
echo "====================\n";
echo "✅ Successfully reset: {$successCount}/{$totalTables} tables\n";

if ($successCount === $totalTables) {
    echo "🎯 All core tables reset successfully!\n";
    echo "💡 Next step: Seed the tables with sample data\n";
    echo "   docker exec adfinalproject-service php utils/dbSeederPostgresql.util.php\n";
    echo "\n📝 Note: Orders functionality will be created through the application\n";
    exit(0);
} else {
    echo "⚠️  Some tables failed to reset. Check the errors above.\n";
    exit(1);
}
?>