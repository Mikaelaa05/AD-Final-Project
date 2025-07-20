<?php
declare(strict_types=1);
/**
 * Admin Stock Management Handler - Handle stock updates (No AJAX)
 */

require_once __DIR__ . '/../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';

session_start();

// Check if user is authenticated and is admin
if (!isAuthenticated()) {
    header('Location: /pages/Login');
    exit;
}

if (!isAdmin()) {
    header('Location: /errors/unauthorized.error.php');
    exit;
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/Admin');
    exit;
}

// Get database connection
global $typeConfig;
$host = $typeConfig['pgHost'];
$port = $typeConfig['pgPort'];
$username = $typeConfig['pgUser'];
$password = $typeConfig['pgPass'];
$dbname = $typeConfig['pgDb'];

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$action = $_POST['action'] ?? '';
$productId = $_POST['product_id'] ?? '';

try {
    switch ($action) {
        case 'increase':
            if (empty($productId)) {
                throw new Exception('Invalid product ID');
            }
            
            // Increase stock by 1
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + 1 WHERE id = :product_id");
            $success = $stmt->execute([':product_id' => $productId]);
            
            if ($success) {
                header('Location: /pages/Admin?stock_updated=increased');
            } else {
                header('Location: /pages/Admin?error=update_failed');
            }
            break;
            
        case 'decrease':
            if (empty($productId)) {
                throw new Exception('Invalid product ID');
            }
            
            // Decrease stock by 1 (but not below 0)
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = GREATEST(stock_quantity - 1, 0) WHERE id = :product_id");
            $success = $stmt->execute([':product_id' => $productId]);
            
            if ($success) {
                header('Location: /pages/Admin?stock_updated=decreased');
            } else {
                header('Location: /pages/Admin?error=update_failed');
            }
            break;
            
        case 'set':
            if (empty($productId)) {
                throw new Exception('Invalid product ID');
            }
            
            $newQuantity = (int)($_POST['quantity'] ?? 0);
            if ($newQuantity < 0) {
                throw new Exception('Stock quantity cannot be negative');
            }
            
            // Set specific stock quantity
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = :quantity WHERE id = :product_id");
            $success = $stmt->execute([
                ':quantity' => $newQuantity,
                ':product_id' => $productId
            ]);
            
            if ($success) {
                header('Location: /pages/Admin?stock_updated=set');
            } else {
                header('Location: /pages/Admin?error=update_failed');
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log("Admin stock handler error: " . $e->getMessage());
    header('Location: /pages/Admin?error=' . urlencode($e->getMessage()));
}

exit;
