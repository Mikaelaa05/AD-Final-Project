<?php
declare(strict_types=1);
/**
 * PostgreSQL Database Reset Utility - Auto-detect Tables
 * Drops and recreates available database tables
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

echo "🧹 **POSTGRESQL DATABASE RESET - AUTO-DETECT**\n";
echo "===============================================\n\n";

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

echo "⚠️  **WARNING: DATABASE RESET ONLY**\n";
echo "====================================\n";
echo "🎯 This will reset detected PostgreSQL tables!\n";
echo "📁 Database path: {$databasePath}\n";
echo "📊 Found {$totalTables} tables to reset: " . implode(', ', $tables) . "\n";
echo "💾 All existing data will be permanently lost!\n";
echo "📋 Tables will be recreated empty (no data seeding)\n\n";

echo "🔄 **RESET PROCESS STARTING**\n";
echo "============================\n\n";

foreach ($tables as $table) {
    echo "🔄 **Resetting {$table} table**\n";
    
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
            
            // Drop existing table with CASCADE to handle dependencies
            echo "   🗑️  Dropping existing {$table} table...\n";
            $pdo->exec("DROP TABLE IF EXISTS {$table} CASCADE;");
            echo "   ✅ Table dropped successfully\n";
        } else {
            echo "   📋 Table doesn't exist yet\n";
        }
        
        // Find and apply model file
        $modelPath = $databasePath . "/{$table}.model.sql";
        
        if (file_exists($modelPath)) {
            echo "   📁 Found model file: {$table}.model.sql\n";
            
            $sql = file_get_contents($modelPath);
            if ($sql !== false && !empty(trim($sql))) {
                echo "   🏗️  Recreating {$table} table from schema...\n";
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
                    echo "   ✅ Table recreated successfully (empty)\n";
                    
                    // Show table info
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
                    echo "   ❌ Table creation verification failed\n";
                }
            } else {
                echo "   ❌ Model file is empty or unreadable\n";
            }
        } else {
            echo "   ❌ Model file not found: {$modelPath}\n";
        }
        
    } catch (PDOException $e) {
        echo "   ❌ Reset failed for {$table}: " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        echo "   ❌ Error processing {$table}: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "🎉 **RESET SUMMARY**\n";
echo "====================\n";
echo "✅ Successfully reset: {$successCount}/{$totalTables} tables\n";

// Show final status
echo "\n📊 **POST-RESET STATUS**\n";
echo "========================\n";

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
            echo "✅ {$table}: Ready ({$count} records)\n";
        } else {
            echo "❌ {$table}: Missing\n";
        }
    } catch (Exception $e) {
        echo "❌ {$table}: Error - {$e->getMessage()}\n";
    }
}

if ($successCount === $totalTables) {
    echo "\n🎯 All available tables reset successfully!\n";
    echo "📋 Database structure is ready with empty tables\n";
    echo "\n💡 **NEXT STEPS:**\n";
    echo "================\n";
    echo "🌱 To populate with sample data:\n";
    echo "   docker exec adfinalproject-service php utils/dbSeederPostgresql.util.php\n";
    exit(0);
} else {
    echo "\n⚠️  Some tables failed to reset completely.\n";
    exit(1);
}
?>