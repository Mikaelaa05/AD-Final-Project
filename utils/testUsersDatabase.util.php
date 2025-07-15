<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'bootstrap.php';
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

echo "👥 Testing Users Database...\n\n";

// Load and seed users
$users = require DUMMIES_PATH . '/users.staticData.php';
echo "Seeding users…\n";

$stmt = $pdo->prepare("
    INSERT INTO users (id, username, email, password, first_name, last_name, role, is_active)
    VALUES (:id, :username, :email, :password, :first_name, :last_name, :role, :is_active)
");

foreach ($users as $u) {
    $uuid = generate_uuid();
    try {
        $stmt->execute([
            ':id' => $uuid,
            ':username' => $u['username'],
            ':email' => $u['username'] . '@company.com', // Generate email from username
            ':password' => password_hash($u['password'], PASSWORD_DEFAULT),
            ':first_name' => $u['first_name'],
            ':last_name' => $u['last_name'],
            ':role' => $u['role'],
            ':is_active' => true
        ]);
        echo "✅ Added user: {$u['username']} ({$u['role']})\n";
    } catch (PDOException $e) {
        echo "❌ Error adding {$u['username']}: " . $e->getMessage() . "\n";
    }
}

// Query and display users
echo "\n👥 Users in database:\n";
$stmt = $pdo->query("SELECT username, first_name, last_name, email, role FROM users ORDER BY role, username");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo "- {$user['username']} | {$user['first_name']} {$user['last_name']} | {$user['email']} | {$user['role']}\n";
}

echo "\n✅ Users database test complete!\n";
