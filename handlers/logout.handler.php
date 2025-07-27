<?php

require_once __DIR__ . '/../bootstrap.php';
require_once UTILS_PATH . '/envSetter.util.php';
require_once UTILS_PATH . '/cart.util.php';

session_start();

// Restore stock for items in cart before logout
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    try {
        // Database connection
        $host = $typeConfig['pgHost'];
        $port = $typeConfig['pgPort'];
        $username = $typeConfig['pgUser'];
        $password = $typeConfig['pgPass'];
        $dbname = $typeConfig['pgDb'];

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        // Restore stock for cart items
        restoreCartStock($pdo);
        
    } catch (Exception $e) {
        error_log("Error restoring stock during logout: " . $e->getMessage());
    }
}

session_destroy();
header('Location: /pages/Login/index.php');
exit;