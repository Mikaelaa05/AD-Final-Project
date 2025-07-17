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

echo "🏗️  Creating products table schema…\n";

// Drop existing table if it exists
echo "Dropping existing products table if it exists…\n";
$pdo->exec("DROP TABLE IF EXISTS products CASCADE;");

// Apply products schema
$sql = file_get_contents(DATABASE_PATH . '/products.model.sql');
if ($sql === false) {
    throw new RuntimeException("❌ Could not read products.model.sql");
}

$pdo->exec($sql);
echo "✅ Products table schema created successfully!\n";
