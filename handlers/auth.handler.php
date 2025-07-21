<?php
require_once UTILS_PATH . '/auth.util.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple database connection
$host = 'adfinalproject-postgresql';
$port = 5432;
$username = 'user';
$password = 'password';
$dbname = 'ad_final_project_db';
$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = findUserOrCustomerByUsername($pdo, $username);
    if ($user && verifyPassword($password, $user['password'])) {
        $_SESSION['user'] = $user;
        
        // Redirect based on account type
        if ($user['account_type'] === 'user' && isAdmin()) {
            header('Location: /pages/Admin');
        } else {
            header('Location: /pages/Shop');
        }
        exit;
    }
    
    $error = "Invalid credentials.";
}