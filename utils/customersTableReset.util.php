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

echo "🧹 Resetting customers table…\n";

// Create table if not exists
$sql = file_get_contents('database/customers.model.sql');
if ($sql === false) {
    throw new RuntimeException("❌ Could not read customers.model.sql");
}
$pdo->exec($sql);

// Truncate table (clean all data)
try {
    $pdo->exec("TRUNCATE TABLE customers RESTART IDENTITY CASCADE;");
    echo "✅ Customers table reset successfully!\n";
} catch (PDOException $e) {
    echo "❌ Could not reset customers table: " . $e->getMessage() . "\n";
}
