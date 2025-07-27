<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';
require_once UTILS_PATH . '/cart.util.php';

session_start();

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    error_log("Checkout error: User not authenticated");
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Database connection
$host = $typeConfig['pgHost'];
$port = $typeConfig['pgPort'];
$username = $typeConfig['pgUser'];
$password = $typeConfig['pgPass'];
$dbname = $typeConfig['pgDb'];
$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    error_log("Database connection successful");
    
    // Get user info
    $user = $_SESSION['user'];
    $userId = $user['id'];
    
    // Check if this user exists in customers table
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE id = ?");
    $stmt->execute([$userId]);
    $customerExists = $stmt->fetch();
    
    if (!$customerExists) {
        error_log("Checkout error: User ID " . $userId . " not found in customers table");
        echo json_encode(['success' => false, 'message' => 'Only customers can place orders']);
        exit;
    }
    
    error_log("Customer validation successful for ID: " . $userId);
    
    // Get cart items from session
    $cartItems = $_SESSION['cart'] ?? [];

    if (empty($cartItems)) {
        error_log("Checkout error: Cart is empty");
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }

    error_log("Checkout started for customer: " . $userId . " with " . count($cartItems) . " items");

    // Log cart contents for debugging
    foreach ($cartItems as $item) {
        error_log("Cart item: " . json_encode($item));
    }

    // Start transaction
    $pdo->beginTransaction();
    error_log("Transaction started");

    $customerId = $userId;
    error_log("Customer ID: " . $customerId);
    
    // Calculate totals
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $taxAmount = round($subtotal * 0.08, 2); // 8% tax
    $shippingAmount = $subtotal > 200 ? 0 : 15.99; // Free shipping over $200
    $totalAmount = round($subtotal + $taxAmount + $shippingAmount, 2);

    // Generate order number
    $stmt = $pdo->prepare("SELECT generate_order_number() as order_number");
    $stmt->execute();
    $orderNumber = $stmt->fetch(PDO::FETCH_ASSOC)['order_number'];
    error_log("Generated order number: " . $orderNumber);

    // Create the order
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            customer_id, order_number, status, total_amount, subtotal, 
            tax_amount, shipping_amount, payment_method, payment_status,
            order_date
        ) VALUES (
            :customer_id, :order_number, 'confirmed', :total_amount, :subtotal,
            :tax_amount, :shipping_amount, 'cash_on_delivery', 'pending',
            CURRENT_TIMESTAMP
        ) RETURNING id
    ");
    
    $stmt->execute([
        ':customer_id' => $customerId,
        ':order_number' => $orderNumber,
        ':total_amount' => $totalAmount,
        ':subtotal' => $subtotal,
        ':tax_amount' => $taxAmount,
        ':shipping_amount' => $shippingAmount
    ]);
    
    $orderId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

    // Add order items
    foreach ($cartItems as $item) {
        $totalPrice = round($item['price'] * $item['quantity'], 2);
        
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
            VALUES (:order_id, :product_id, :quantity, :unit_price, :total_price)
        ");
        
        $stmt->execute([
            ':order_id' => $orderId,
            ':product_id' => $item['id'],
            ':quantity' => $item['quantity'],
            ':unit_price' => $item['price'],
            ':total_price' => $totalPrice
        ]);

        // Update product stock (decrease by quantity ordered)
        $stmt = $pdo->prepare("
            UPDATE products 
            SET stock_quantity = stock_quantity - :quantity 
            WHERE id = :product_id AND stock_quantity >= :quantity
        ");
        
        $result = $stmt->execute([
            ':quantity' => $item['quantity'],
            ':product_id' => $item['id']
        ]);

        // Check if stock was sufficient
        if ($stmt->rowCount() === 0) {
            throw new Exception("Insufficient stock for product: " . $item['name']);
        }
    }

    // Commit transaction
    $pdo->commit();

    // Clear the cart
    $_SESSION['cart'] = [];

    // Log the successful order
    error_log("Order created successfully: " . $orderNumber . " for customer: " . $customerId);

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_number' => $orderNumber,
        'order_id' => $orderId,
        'total_amount' => $totalAmount
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Checkout error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Order processing failed: ' . $e->getMessage()
    ]);
}
?>