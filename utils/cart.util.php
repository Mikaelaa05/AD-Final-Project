<?php
declare(strict_types=1);
/**
 * Cart Utility Functions
 * Handle shopping cart operations
 */

require_once __DIR__ . '/../bootstrap.php';
require_once UTILS_PATH . '/envSetter.util.php';

/**
 * Get database connection
 */
function getCartDbConnection(): PDO {
    global $typeConfig;
    
    $host = $typeConfig['pgHost'];
    $port = $typeConfig['pgPort'];
    $username = $typeConfig['pgUser'];
    $password = $typeConfig['pgPass'];
    $dbname = $typeConfig['pgDb'];

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}

/**
 * Add item to cart
 */
function addToCart(string $userId, string $productId, int $quantity = 1): bool {
    try {
        $pdo = getCartDbConnection();
        
        // Check if item already exists in cart
        $checkSql = "SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId
        ]);
        
        $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingItem) {
            // Update quantity if item exists
            $newQuantity = $existingItem['quantity'] + $quantity;
            $updateSql = "UPDATE cart SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $updateStmt = $pdo->prepare($updateSql);
            return $updateStmt->execute([
                ':quantity' => $newQuantity,
                ':id' => $existingItem['id']
            ]);
        } else {
            // Insert new item
            $insertSql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
            $insertStmt = $pdo->prepare($insertSql);
            return $insertStmt->execute([
                ':user_id' => $userId,
                ':product_id' => $productId,
                ':quantity' => $quantity
            ]);
        }
    } catch (PDOException $e) {
        error_log("Cart add error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get cart items for user
 */
function getCartItems(string $userId): array {
    try {
        $pdo = getCartDbConnection();
        
        $sql = "SELECT c.id, c.quantity, c.added_at, c.updated_at,
                       p.id as product_id, p.name, p.description, p.price, p.sku, p.category, p.stock_quantity
                FROM cart c
                INNER JOIN products p ON c.product_id = p.id
                WHERE c.user_id = :user_id
                ORDER BY c.updated_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get cart items error: " . $e->getMessage());
        return [];
    }
}

/**
 * Update cart item quantity
 */
function updateCartQuantity(string $userId, string $productId, int $quantity): bool {
    try {
        $pdo = getCartDbConnection();
        
        if ($quantity <= 0) {
            return removeFromCart($userId, $productId);
        }
        
        $sql = "UPDATE cart SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP 
                WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':quantity' => $quantity,
            ':user_id' => $userId,
            ':product_id' => $productId
        ]);
    } catch (PDOException $e) {
        error_log("Update cart quantity error: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove item from cart
 */
function removeFromCart(string $userId, string $productId): bool {
    try {
        $pdo = getCartDbConnection();
        
        $sql = "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId
        ]);
    } catch (PDOException $e) {
        error_log("Remove from cart error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get cart item count for user
 */
function getCartItemCount(string $userId): int {
    try {
        $pdo = getCartDbConnection();
        
        $sql = "SELECT COALESCE(SUM(quantity), 0) as total_items FROM cart WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total_items'] ?? 0);
    } catch (PDOException $e) {
        error_log("Get cart item count error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get cart total amount
 */
function getCartTotal(string $userId): float {
    try {
        $pdo = getCartDbConnection();
        
        $sql = "SELECT COALESCE(SUM(c.quantity * p.price), 0) as total_amount
                FROM cart c
                INNER JOIN products p ON c.product_id = p.id
                WHERE c.user_id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total_amount'] ?? 0.00);
    } catch (PDOException $e) {
        error_log("Get cart total error: " . $e->getMessage());
        return 0.00;
    }
}

/**
 * Clear all items from cart for user
 */
function clearCart(string $userId): bool {
    try {
        $pdo = getCartDbConnection();
        
        $sql = "DELETE FROM cart WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    } catch (PDOException $e) {
        error_log("Clear cart error: " . $e->getMessage());
        return false;
    }
}

/**
 * Checkout cart - convert cart items to order and decrease stock
 */
function checkoutCart(string $userId, array $shippingInfo = []): array {
    try {
        $pdo = getCartDbConnection();
        $pdo->beginTransaction();
        
        // Get cart items
        $cartItems = getCartItems($userId);
        
        if (empty($cartItems)) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }
        
        // Check stock availability
        foreach ($cartItems as $item) {
            if ($item['quantity'] > $item['stock_quantity']) {
                $pdo->rollBack();
                return [
                    'success' => false, 
                    'message' => "Insufficient stock for {$item['name']}. Available: {$item['stock_quantity']}, Requested: {$item['quantity']}"
                ];
            }
        }
        
        // Generate order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . uniqid();
        
        // Calculate totals
        $subtotal = getCartTotal($userId);
        $taxAmount = $subtotal * 0.1; // 10% tax
        $shippingAmount = $subtotal > 100 ? 0 : 10; // Free shipping over $100
        $totalAmount = $subtotal + $taxAmount + $shippingAmount;
        
        // Create order
        $orderSql = "INSERT INTO orders (customer_id, order_number, status, total_amount, subtotal, tax_amount, shipping_amount, shipping_address, payment_status) 
                     VALUES (:customer_id, :order_number, 'pending', :total_amount, :subtotal, :tax_amount, :shipping_amount, :shipping_address, 'pending')";
        $orderStmt = $pdo->prepare($orderSql);
        $orderStmt->execute([
            ':customer_id' => $userId,
            ':order_number' => $orderNumber,
            ':total_amount' => $totalAmount,
            ':subtotal' => $subtotal,
            ':tax_amount' => $taxAmount,
            ':shipping_amount' => $shippingAmount,
            ':shipping_address' => json_encode($shippingInfo)
        ]);
        
        // Get the order ID
        $orderId = $pdo->lastInsertId();
        if (!$orderId) {
            // If lastInsertId() doesn't work with UUID, get it manually
            $getOrderSql = "SELECT id FROM orders WHERE order_number = :order_number";
            $getOrderStmt = $pdo->prepare($getOrderSql);
            $getOrderStmt->execute([':order_number' => $orderNumber]);
            $orderResult = $getOrderStmt->fetch(PDO::FETCH_ASSOC);
            $orderId = $orderResult['id'];
        }
        
        // Create order items and decrease stock
        foreach ($cartItems as $item) {
            // Insert order item
            $orderItemSql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) 
                             VALUES (:order_id, :product_id, :quantity, :unit_price, :total_price)";
            $orderItemStmt = $pdo->prepare($orderItemSql);
            $orderItemStmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $item['product_id'],
                ':quantity' => $item['quantity'],
                ':unit_price' => $item['price'],
                ':total_price' => $item['quantity'] * $item['price']
            ]);
            
            // Decrease product stock
            $updateStockSql = "UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :product_id";
            $updateStockStmt = $pdo->prepare($updateStockSql);
            $updateStockStmt->execute([
                ':quantity' => $item['quantity'],
                ':product_id' => $item['product_id']
            ]);
        }
        
        // Clear cart
        clearCart($userId);
        
        $pdo->commit();
        
        return [
            'success' => true, 
            'message' => 'Order placed successfully',
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'total_amount' => $totalAmount
        ];
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Checkout error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Checkout failed: ' . $e->getMessage()];
    }
}
