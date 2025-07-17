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

echo "🔍 Verifying users database…\n";

try {
    // Check if table exists
    $result = $pdo->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'users'
        )
    ");
    $tableExists = $result->fetchColumn();
    
    if (!$tableExists) {
        echo "❌ Users table does not exist!\n";
        exit(1);
    }
    
    echo "✅ Users table exists\n";
    
    // Get total user count
    $result = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $result->fetchColumn();
    echo "📊 Total users: {$totalUsers}\n";
    
    if ($totalUsers > 0) {
        // User role breakdown
        echo "\n📈 User Role Breakdown:\n";
        $result = $pdo->query("
            SELECT 
                role,
                COUNT(*) as count
            FROM users 
            GROUP BY role
            ORDER BY count DESC
        ");
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['role']}: {$row['count']} users\n";
        }
        
        // Sample user data
        echo "\n📝 Sample User Records:\n";
        $result = $pdo->query("
            SELECT 
                username,
                first_name,
                last_name,
                role
            FROM users 
            LIMIT 5
        ");
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  • {$row['username']}: {$row['first_name']} {$row['last_name']} ({$row['role']})\n";
        }
    } else {
        echo "⚠️  No users found in database\n";
    }
    
    echo "✅ Users database verification complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
