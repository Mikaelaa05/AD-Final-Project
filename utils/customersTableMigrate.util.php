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

echo "🏗️  Creating customers table schema…\n";

// Drop existing table if it exists
echo "Dropping existing customers table if it exists…\n";
$pdo->exec("DROP TABLE IF EXISTS customers CASCADE;");

// Apply customers schema
$sql = file_get_contents('database/customers.model.sql');
if ($sql === false) {
    throw new RuntimeException("❌ Could not read customers.model.sql");
}

$pdo->exec($sql);
echo "✅ Customers table schema created successfully!\n";
