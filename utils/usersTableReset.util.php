<?php
declare(strict_types=1);

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

echo "🧹 Resetting users table…\n";

// Create table if not exists
$sql = file_get_contents(DATABASE_PATH . '/users.model.sql');
if ($sql === false) {
    throw new RuntimeException("❌ Could not read users.model.sql");
}
$pdo->exec($sql);

// Truncate table (clean all data)
try {
    $pdo->exec("TRUNCATE TABLE users RESTART IDENTITY CASCADE;");
    echo "✅ Users table reset successfully!\n";
} catch (PDOException $e) {
    echo "❌ Could not reset users table: " . $e->getMessage() . "\n";
}
