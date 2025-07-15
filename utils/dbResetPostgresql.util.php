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

// 1. Apply schema for each table (create tables if not exist)
$models = [
    'database/users.model.sql',
    'database/products.model.sql',
    'database/projects.model.sql',
    'database/project_users.model.sql',
    'database/tasks.model.sql',
];

foreach ($models as $model) {
    echo "Applying schema from {$model}…\n";
    $sql = file_get_contents($model);

    if ($sql === false) {
        throw new RuntimeException("❌ Could not read {$model}");
    } else {
        echo "Creation Success from {$model}\n";
    }

    $pdo->exec($sql);
}

// 2. Truncate tables (clean all data, restart identity)
echo "Truncating tables…\n";
foreach (['project_users', 'tasks', 'projects', 'products', 'users'] as $table) {
    try {
        $pdo->exec("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE;");
    } catch (PDOException $e) {
        echo "Warning: Could not truncate table {$table}: " . $e->getMessage() . "\n";
    }
}

echo "✅ PostgreSQL reset complete!\n";