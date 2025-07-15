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

echo "🏗️  Creating users table schema…\n";

// Drop existing table if it exists
echo "Dropping existing users table if it exists…\n";
$pdo->exec("DROP TABLE IF EXISTS users CASCADE;");

// Apply users schema
$sql = file_get_contents('database/users.model.sql');
if ($sql === false) {
    throw new RuntimeException("❌ Could not read users.model.sql");
}

$pdo->exec($sql);
echo "✅ Users table schema created successfully!\n";
