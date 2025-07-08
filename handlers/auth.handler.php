<?php
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connect to DB
$host = $typeConfig['pgHost'];
$port = $typeConfig['pgPort'];
$username = $typeConfig['pgUser'];
$password = $typeConfig['pgPass'];
$dbname = $typeConfig['pgDb'];
$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = findUserByUsername($pdo, $username);
    if ($user && verifyPassword($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: /');
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}