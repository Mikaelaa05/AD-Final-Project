<?php
declare(strict_types=1);
/**
 * PostgreSQL Database Migration Utility - All Tables
 * Applies all database schemas from model files
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

echo "🏗️  **POSTGRESQL DATABASE MIGRATION - ALL TABLES**\n";
echo "=========================================================\n\n";

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

foreach ($tables as $table => $description) {
    echo "📋 **Migrating {$table} table**\n";
    echo "   Purpose: {$description}\n";
    
    try {
        // Drop existing table if it exists
        echo "   🗑️  Dropping existing {$table} table if exists...\n";
        $pdo->exec("DROP TABLE IF EXISTS {$table} CASCADE;");
        
        // Read model file
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
        
        // Apply schema
        $pdo->exec($sql);
        echo "   ✅ Schema applied successfully\n";
        
        // Verify table creation
        $result = $pdo->query("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = '{$table}'
            )
        ");
        
        if ($result->fetchColumn()) {
            echo "   ✅ Table verified and ready\n";
            $successCount++;
        } else {
            echo "   ❌ Table verification failed\n";
        }
        
    } catch (PDOException $e) {
        echo "   ❌ Migration failed: " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "🎉 **MIGRATION SUMMARY**\n";
echo "========================\n";
echo "✅ Successfully migrated: {$successCount}/{$totalTables} tables\n";

if ($successCount === $totalTables) {
    echo "🎯 All tables migrated successfully!\n";
    echo "💡 Next step: Seed the tables with sample data\n";
    echo "   docker exec adfinalproject-service php utils/dbSeederPostgresql.util.php\n";
    exit(0);
} else {
    echo "⚠️  Some tables failed to migrate. Check the errors above.\n";
    exit(1);
}
?>