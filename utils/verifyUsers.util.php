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

echo "👥 **USERS DATABASE VERIFICATION**\n";
echo "==================================\n\n";

// Get user count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total = $stmt->fetchColumn();
echo "Total Users in Database: {$total}\n\n";

// Get users by role
$stmt = $pdo->query("
    SELECT role, COUNT(*) as count
    FROM users 
    GROUP BY role 
    ORDER BY role
");

echo "📊 **USERS BY ROLE:**\n";
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($roles as $role) {
    echo "• {$role['role']}: {$role['count']} users\n";
}

// Get all users
echo "\n👥 **COMPLETE USER LIST:**\n";
$stmt = $pdo->query("
    SELECT username, first_name, last_name, email, role, is_active, created_at
    FROM users 
    ORDER BY role, username
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    $status = $u['is_active'] ? '🟢 Active' : '🔴 Inactive';
    $created = date('Y-m-d', strtotime($u['created_at']));
    echo "• {$u['username']}\n";
    echo "  Name: {$u['first_name']} {$u['last_name']}\n";
    echo "  Email: {$u['email']}\n";
    echo "  Role: {$u['role']}\n";
    echo "  Status: {$status}\n";
    echo "  Created: {$created}\n\n";
}

echo "✅ **Users Database Verification Complete!**\n";
