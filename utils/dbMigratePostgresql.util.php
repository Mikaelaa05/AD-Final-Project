<?php
declare(strecho "Dropping old tables…\n";
foreach (['project_users', 'tasks', 'projects', 'products', 'users'] as $table) {
    // Use IF EXISTS so it won't error if the table is already gone
    $pdo->exec("DROP TABLE IF EXISTS {$table} CASCADE;");
}

$models = [
    'database/users.model.sql',
    'database/products.model.sql',
    'database/projects.model.sql',
    'database/project_users.model.sql',
    'database/tasks.model.sql',
];;

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

echo "Dropping old tables…\n";
foreach (['project_users', 'tasks', 'projects', 'users'] as $table) {
    // Use IF EXISTS so it won’t error if the table is already gone
    $pdo->exec("DROP TABLE IF EXISTS {$table} CASCADE;");
}

$models = [
    'database/users.model.sql',
    'database/projects.model.sql',
    'database/project_users.model.sql',
    'database/tasks.model.sql',
];

foreach ($models as $model) {
    echo "Applying schema from {$model}…\n";
    $sql = file_get_contents($model);

    if ($sql === false) {
        throw new RuntimeException("Could not read {$model}");
    } else {
        echo "Creation Success from {$model}\n";
    }

    $pdo->exec($sql);
}

echo "✅ PostgreSQL migration complete!\n";