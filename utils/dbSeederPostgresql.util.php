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

// 1. Apply schema for each table (create tables if not exist)
$models = [
    'database/users.model.sql',
    'database/projects.model.sql',
    'database/project_users.model.sql',
    'database/tasks.model.sql',
    'database/products.model.sql',
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
foreach (['project_users', 'tasks', 'projects', 'users', 'products'] as $table) {
    try {
        $pdo->exec("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE;");
    } catch (PDOException $e) {
        echo "Warning: Could not truncate table {$table}: " . $e->getMessage() . "\n";
    }
}

// 3. Seed users table
$users = require DUMMIES_PATH . '/users.staticData.php';
echo "Seeding users…\n";
$stmt = $pdo->prepare("
    INSERT INTO users (id, username, role, first_name, last_name, password)
    VALUES (:id, :username, :role, :fn, :ln, :pw)
");
$userIds = [];
foreach ($users as $u) {
    $uuid = generate_uuid();
    $stmt->execute([
        ':id' => $uuid,
        ':username' => $u['username'],
        ':role' => $u['role'],
        ':fn' => $u['first_name'],
        ':ln' => $u['last_name'],
        ':pw' => password_hash($u['password'], PASSWORD_DEFAULT),
    ]);
    $userIds[] = $uuid;
}

// 4. Seed projects table
$projects = require DUMMIES_PATH . '/projects.staticData.php';
echo "Seeding projects…\n";
$stmt = $pdo->prepare("
    INSERT INTO projects (id, name, description)
    VALUES (:id, :name, :desc)
");
$projectIds = [];
foreach ($projects as $p) {
    $uuid = generate_uuid();
    $stmt->execute([
        ':id' => $uuid,
        ':name' => $p['name'],
        ':desc' => $p['description'],
    ]);
    $projectIds[] = $uuid;
}

// 5. Seed tasks table
$tasks = require DUMMIES_PATH . '/tasks.staticData.php';
echo "Seeding tasks…\n";
$stmt = $pdo->prepare("
    INSERT INTO tasks (id, project_id, assigned_to, title, description, status, due_date)
    VALUES (:id, :project_id, :assigned_to, :title, :desc, :status, :due_date)
");
foreach ($tasks as $t) {
    $uuid = generate_uuid();
    $stmt->execute([
        ':id' => $uuid,
        ':project_id' => $projectIds[0] ?? null,
        ':assigned_to' => $userIds[0] ?? null,
        ':title' => $t['title'],
        ':desc' => $t['description'],
        ':status' => $t['status'],
        ':due_date' => $t['due_date'],
    ]);
}

// 6. Seed products table
$products = require DUMMIES_PATH . '/products.staticData.php';
echo "Seeding products…\n";
$stmt = $pdo->prepare("
    INSERT INTO products (id, name, description, price, category, stock_quantity, sku, status)
    VALUES (:id, :name, :description, :price, :category, :stock_quantity, :sku, :status)
");
foreach ($products as $p) {
    $uuid = generate_uuid();
    $stmt->execute([
        ':id' => $uuid,
        ':name' => $p['name'],
        ':description' => $p['description'],
        ':price' => $p['price'],
        ':category' => $p['category'],
        ':stock_quantity' => $p['stock_quantity'],
        ':sku' => $p['sku'],
        ':status' => $p['status'],
    ]);
}

// 7. Seed project_users table
$projectUsers = require DUMMIES_PATH . '/project_users.staticData.php';
echo "Seeding project_users…\n";
$stmt = $pdo->prepare("
    INSERT INTO project_users (project_id, user_id)
    VALUES (:project_id, :user_id)
");
foreach ($projectUsers as $pu) {
    $stmt->execute([
        ':project_id' => $projectIds[0] ?? null,
        ':user_id' => $userIds[0] ?? null,
    ]);
}

echo "✅ PostgreSQL seeding complete!\n";