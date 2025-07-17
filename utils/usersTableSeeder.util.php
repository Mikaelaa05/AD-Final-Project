<?php
declare(strict_types=1);

require_once BASE_PATH . '/vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';
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

echo "🌱 Seeding users table…\n";

// Create table if not exists
$sql = file_get_contents(DATABASE_PATH . '/users.model.sql');
if ($sql === false) {
    throw new RuntimeException("❌ Could not read users.model.sql");
}
$pdo->exec($sql);

// Clear existing data
try {
    $pdo->exec("TRUNCATE TABLE users RESTART IDENTITY CASCADE;");
} catch (PDOException $e) {
    echo "Warning: Could not truncate users table: " . $e->getMessage() . "\n";
}

// Seed users data
$users = require DUMMIES_PATH . '/users.staticData.php';
$stmt = $pdo->prepare("
    INSERT INTO users (id, username, email, phone, password, first_name, last_name, role, is_active)
    VALUES (:id, :username, :email, :phone, :password, :fn, :ln, :role, :is_active)
");

$userCount = 0;
foreach ($users as $u) {
    $uuid = generate_uuid();
    $email = $u['email'] ?? (strtolower($u['username']) . '@adfinalproject.dev');
    $phone = $u['phone'] ?? null;
    $stmt->execute([
        ':id' => $uuid,
        ':username' => $u['username'],
        ':email' => $email,
        ':phone' => $phone,
        ':password' => password_hash($u['password'], PASSWORD_DEFAULT),
        ':fn' => $u['first_name'],
        ':ln' => $u['last_name'],
        ':role' => $u['role'],
        ':is_active' => true,
    ]);
    $userCount++;
}

echo "✅ Successfully seeded {$userCount} users!\n";

// Display seeded data summary
echo "\n📊 Users Database Summary:\n";
$result = $pdo->query("
    SELECT 
        role,
        COUNT(*) as count
    FROM users 
    GROUP BY role
");

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$row['role']}: {$row['count']} users\n";
}
