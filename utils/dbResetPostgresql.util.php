<?php
declare(strict_types=1);
/**
 * PostgreSQL Database Reset Utility - All Tables
 * Drops and recreates all database tables
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

echo "🧹 **POSTGRESQL DATABASE RESET - ALL TABLES**\n";
echo "==============================================\n\n";

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

echo "⚠️  WARNING: This will completely reset all PostgreSQL tables!\n";
echo "📊 Tables to reset: " . implode(', ', array_keys($tables)) . "\n\n";

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
        
        // Clear all data
        echo "   🧹 Truncating table data...\n";
        try {
            $pdo->exec("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE;");
        } catch (PDOException $e) {
            // Table might be empty or have constraints
            echo "   💡 Truncate skipped (table may be empty): " . $e->getMessage() . "\n";
        }
        
        // Verify reset
        $result = $pdo->query("SELECT COUNT(*) FROM {$table}");
        $finalCount = $result->fetchColumn();
        
        echo "   ✅ Table reset successfully (records: {$finalCount})\n";
        $successCount++;
        
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
    echo "🎯 All tables reset successfully!\n";
    echo "💡 Next step: Seed the tables with sample data\n";
    echo "   docker exec adfinalproject-service php utils/dbSeederPostgresql.util.php\n";
    exit(0);
} else {
    echo "⚠️  Some tables failed to reset. Check the errors above.\n";
    exit(1);
}
?>