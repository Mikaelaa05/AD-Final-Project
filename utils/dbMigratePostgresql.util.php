<?php
declare(strict_types=1);
/**
 * PostgreSQL Database Migration Utility - Auto-detect Tables
 * Creates database tables from available model files
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

echo "🏗️  **POSTGRESQL DATABASE MIGRATION - AUTO-DETECT**\n";
echo "====================================================\n\n";

// Auto-detect available model files
$databasePath = BASE_PATH . '/database';
$modelFiles = glob($databasePath . '/*.model.sql');

if (empty($modelFiles)) {
    echo "❌ No model files found in: {$databasePath}\n";
    echo "💡 Please create .model.sql files for your tables\n";
    exit(1);
}

$tables = [];
foreach ($modelFiles as $filePath) {
    $filename = basename($filePath);
    $tableName = str_replace('.model.sql', '', $filename);
    $tables[] = $tableName;
}

$successCount = 0;
$totalTables = count($tables);

echo "📝 **MIGRATION PLAN**\n";
echo "===================\n";
echo "🎯 Purpose: Create table structures only\n";
echo "📁 Database path: {$databasePath}\n";
echo "📊 Found {$totalTables} model files: " . implode(', ', $tables) . "\n";
echo "📋 Result: Empty tables ready for data\n\n";

foreach ($tables as $table) {
    echo "📋 **Migrating {$table} table**\n";

    try {
        $modelPath = $databasePath . "/{$table}.model.sql";
        
        // Read model file
        $sql = file_get_contents($modelPath);
        if ($sql === false || empty(trim($sql))) {
            echo "   ❌ Model file is empty or unreadable\n";
            echo "   ⏭️  Skipping {$table} table\n\n";
            continue;
        }
        
        // Drop existing table if it exists (with CASCADE for dependencies)
        echo "   🗑️  Dropping existing {$table} table if exists...\n";
        $pdo->exec("DROP TABLE IF EXISTS {$table} CASCADE;");
        
        // Apply schema
        echo "   🏗️  Creating {$table} table from schema...\n";
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
            echo "   ✅ Table created successfully (empty)\n";
            
            // Show table structure
            $columnsResult = $pdo->query("
                SELECT COUNT(*) 
                FROM information_schema.columns 
                WHERE table_schema = 'public' 
                AND table_name = '{$table}'
            ");
            
            $columnCount = $columnsResult->fetchColumn();
            echo "   📊 Structure: {$columnCount} columns ready\n";
            
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

// Show final database structure
echo "\n📊 **DATABASE STRUCTURE**\n";
echo "=========================\n";

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
            $columnsResult = $pdo->query("
                SELECT COUNT(*) 
                FROM information_schema.columns 
                WHERE table_schema = 'public' 
                AND table_name = '{$table}'
            ");
            $columnCount = $columnsResult->fetchColumn();
            echo "✅ {$table}: {$columnCount} columns (empty)\n";
        } else {
            echo "❌ {$table}: Table not found\n";
        }
    } catch (Exception $e) {
        echo "❌ {$table}: Error checking - {$e->getMessage()}\n";
    }
}

if ($successCount === $totalTables) {
    echo "\n🎯 All available tables migrated successfully!\n";
    echo "📋 Database structure is ready with empty tables\n";
    echo "\n💡 **NEXT STEPS:**\n";
    echo "================\n";
    echo "🌱 To populate with sample data:\n";
    echo "   docker exec adfinalproject-service php utils/dbSeederPostgresql.util.php\n";
    exit(0);
} else {
    echo "\n⚠️  Some tables failed to migrate. Check the errors above.\n";
    exit(1);
}
?>