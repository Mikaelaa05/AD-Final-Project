<?php
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
            try {
                $countResult = $pdo->query("SELECT COUNT(*) FROM {$table}");
                $currentCount = $countResult->fetchColumn();
                echo "   📊 Current records: {$currentCount}\n";
            } catch (Exception $e) {
                echo "   📊 Current records: Unable to count\n";
            }
        } else {
            echo "   📋 Table doesn't exist yet\n";
        }
        
        // Read and apply model file
        $modelPath = DATABASE_PATH . "/{$table}.model.sql";
        
        if (!file_exists($modelPath)) {
            echo "   ❌ Model file not found: {$modelPath}\n";
            echo "   📁 DATABASE_PATH: " . DATABASE_PATH . "\n";
            echo "   ⏭️  Skipping {$table} table\n\n";
            continue;
        }
        
        $sql = file_get_contents($modelPath);
        if ($sql === false || empty(trim($sql))) {
            echo "   ❌ Model file is empty or unreadable\n";
            echo "   ⏭️  Skipping {$table} table\n\n";
            continue;
        }
        
        // Show first 100 characters of SQL for debugging
        echo "   📄 SQL preview: " . substr(trim($sql), 0, 100) . "...\n";
        
        // Drop existing table
        echo "   🗑️  Dropping existing table...\n";
        $pdo->exec("DROP TABLE IF EXISTS {$table} CASCADE;");
        
        // Recreate table with better error handling
        echo "   🏗️  Recreating table from schema...\n";
        try {
            $pdo->exec($sql);
            echo "   ✅ Schema executed successfully\n";
        } catch (PDOException $sqlError) {
            echo "   ❌ SQL execution failed for {$table}:\n";
            echo "   📄 Error: " . $sqlError->getMessage() . "\n";
            echo "   📄 SQL Content:\n";
            echo "   " . str_replace("\n", "\n   ", $sql) . "\n";
            throw $sqlError; // Re-throw to be caught by outer try-catch
        }
        
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
        echo "   ❌ Reset failed for {$table}: " . $e->getMessage() . "\n";
        echo "   📄 Error Code: " . $e->getCode() . "\n";
        // Continue with next table instead of stopping
    } catch (Exception $e) {
        echo "   ❌ Error processing {$table}: " . $e->getMessage() . "\n";
        // Continue with next table instead of stopping
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
    echo "💡 You can run the seeder anyway if enough tables were created:\n";
    echo "   docker exec adfinalproject-service php utils/dbSeederPostgresql.util.php\n";
    exit(1);
}
?>