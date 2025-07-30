<?php
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/errorHandler.util.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
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
} catch (PDOException $e) {
    error_log("Database connection failed in auth handler: " . $e->getMessage());
    ErrorHandler::databaseError('Unable to connect to authentication system', '/pages/Login/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        ErrorHandler::badRequestError('Username and password are required', '/pages/Login/index.php');
    }

    try {
        $user = findUserOrCustomerByUsername($pdo, $username);
        if ($user && verifyPassword($password, $user['password'])) {
            $_SESSION['user'] = $user;
            
            // Redirect all users to home page after login
            header('Location: /');
            exit;
        }
        
        $error = "Invalid credentials.";
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        ErrorHandler::serverError('Authentication failed. Please try again.', '/pages/Login/index.php');
    }
}