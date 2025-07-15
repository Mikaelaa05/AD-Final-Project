<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'bootstrap.php';
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

echo "🔍 Testing Simplified User Database...\n\n";

// Test 1: Check if users table exists with correct schema
echo "1. Checking users table structure...\n";
try {
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'users' 
        ORDER BY ordinal_position
    ");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   ✅ Users table found with " . count($columns) . " columns:\n";
    foreach ($columns as $col) {
        echo "   - {$col['column_name']} ({$col['data_type']})\n";
    }
} catch (PDOException $e) {
    echo "   ❌ Error checking table structure: " . $e->getMessage() . "\n";
}

// Test 2: Check if we can insert a test user
echo "\n2. Testing user insertion...\n";
try {
    $stmt = $pdo->prepare("
        INSERT INTO users (id, username, email, password, first_name, last_name, role, is_active)
        VALUES (gen_random_uuid(), :username, :email, :password, :first_name, :last_name, :role, :is_active)
        RETURNING id, username, email, role
    ");
    
    $stmt->execute([
        ':username' => 'test.user',
        ':email' => 'test@example.com',
        ':password' => password_hash('TestPass123!', PASSWORD_DEFAULT),
        ':first_name' => 'Test',
        ':last_name' => 'User',
        ':role' => 'user',
        ':is_active' => true
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Test user created successfully:\n";
    echo "   - ID: {$result['id']}\n";
    echo "   - Username: {$result['username']}\n";
    echo "   - Email: {$result['email']}\n";
    echo "   - Role: {$result['role']}\n";
    
    // Clean up test user
    $pdo->prepare("DELETE FROM users WHERE username = 'test.user'")->execute();
    echo "   ✅ Test user cleaned up\n";
    
} catch (PDOException $e) {
    echo "   ❌ Error testing user insertion: " . $e->getMessage() . "\n";
}

// Test 3: Check constraints
echo "\n3. Testing database constraints...\n";
try {
    // Test unique username constraint
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, first_name, last_name) 
        VALUES ('duplicate', 'test1@example.com', 'pass', 'Test', 'User'),
               ('duplicate', 'test2@example.com', 'pass', 'Test', 'User')
    ");
    $stmt->execute();
    $pdo->rollback();
    echo "   ❌ Username uniqueness constraint not working\n";
} catch (PDOException $e) {
    $pdo->rollback();
    if (strpos($e->getMessage(), 'duplicate') !== false) {
        echo "   ✅ Username uniqueness constraint working\n";
    } else {
        echo "   ❌ Unexpected error: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Simplified User Database Test Complete!\n";
