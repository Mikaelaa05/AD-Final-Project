<?php
declare(strict_types=1);

require_once BASE_PATH . '/vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';
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

echo "🔍 **ALL DATABASES STATUS CHECK**\n";
echo "===================================\n\n";

$tables = ['users', 'customers', 'products'];

foreach ($tables as $table) {
    echo "📊 **" . strtoupper($table) . " DATABASE**\n";
    echo str_repeat("-", 20) . "\n";
    
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

echo "✅ **Database status check complete!**\n";
echo "\n📋 **MANAGEMENT COMMANDS:**\n";
echo "Users:     docker exec adfinalproject-service php utils/usersTable[Migrate|Reset|Seeder|Verify].util.php\n";
echo "Customers: docker exec adfinalproject-service php utils/customersTable[Migrate|Reset|Seeder|Verify].util.php\n";
echo "Products:  docker exec adfinalproject-service php utils/productsTable[Migrate|Reset|Seeder|Verify].util.php\n";
