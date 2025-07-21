<?php

/**
 * Cart Utility Functions
 * Provides utility functions for cart operations
 */

/**
 * Get the current cart count for the user
 */
function getCartCount() {
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

/**
 * Get the total number of items in the cart (including quantities)
 */
function getCartItemCount() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $totalItems = 0;
    foreach ($_SESSION['cart'] as $item) {
        $totalItems += $item['quantity'];
    }
    
    return $totalItems;
}

/**
 * Get the cart subtotal
 */
function getCartSubtotal() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    return $subtotal;
}
/**
 * Check if an item is already in the cart
 */
function isItemInCart($productId) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return false;
    }
    
    foreach ($_SESSION['cart'] as $item) {
        if ($item['id'] == $productId) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get the quantity of a specific item in the cart
 */
function getItemQuantityInCart($productId) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    foreach ($_SESSION['cart'] as $item) {
        if ($item['id'] == $productId) {
            return $item['quantity'];
        }
    }
    
    return 0;
}

/**
 * Initialize cart if it doesn't exist
 */
function initializeCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Format currency for display
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}
/**
 * Get cart summary for display
 */
function getCartSummary() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'total' => 0,
            'item_count' => 0,
            'product_count' => 0
        ];
    }
    
    $subtotal = 0;
    $itemCount = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $itemCount += $item['quantity'];
    }
    
    $tax = $subtotal * 0.08; // 8% tax
    $shipping = $subtotal > 200 ? 0 : 15.99; // Free shipping over $200
    $total = $subtotal + $tax + $shipping;
    
    return [
        'subtotal' => $subtotal,
        'tax' => $tax,
        'shipping' => $shipping,
        'total' => $total,
        'item_count' => $itemCount,
        'product_count' => count($_SESSION['cart']),
        'free_shipping_threshold' => 200,
        'amount_for_free_shipping' => max(0, 200 - $subtotal)
    ];
}
/**
 * Validate product data before adding to cart
 */
function validateProduct($pdo, $productId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = true");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error validating product: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if sufficient stock is available
 */
function checkStock($pdo, $productId, $requestedQuantity) {
    try {
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? AND is_active = true");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            return false;
        }
        
        return $product['stock_quantity'] >= $requestedQuantity;
    } catch (PDOException $e) {
        error_log("Error checking stock: " . $e->getMessage());
        return false;
    }
}
/**
 * Sanitize cart data for security
 */
function sanitizeCartData($data) {
    if (is_array($data)) {
        return array_map('sanitizeCartData', $data);
    }
    
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Log cart activity for debugging
 */
function logCartActivity($action, $productId = null, $quantity = null, $userId = null) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'product_id' => $productId,
        'quantity' => $quantity,
        'user_id' => $userId ?? ($_SESSION['user']['id'] ?? 'unknown'),
        'cart_count' => getCartCount()
    ];
    
    error_log("Cart Activity: " . json_encode($logEntry));
}
/**
 * Restore stock for all items in cart (used during logout or session cleanup)
 */
function restoreCartStock($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return true;
    }
    
    try {
        $pdo->beginTransaction();
        
        foreach ($_SESSION['cart'] as $cartItem) {
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$cartItem['quantity'], $cartItem['id']]);
            
            logCartActivity('stock_restore', $cartItem['id'], $cartItem['quantity']);
        }
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error restoring cart stock: " . $e->getMessage());
        return false;
    }
}
/**
 * Clear cart without restoring stock (used when order is completed)
 */
function clearCartAfterOrder() {
    logCartActivity('order_complete', null, getCartItemCount());
    $_SESSION['cart'] = [];
}

/**
 * Get available stock for a product (actual database stock)
 */
function getActualStock($pdo, $productId) {
    try {
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? AND is_active = true");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $product ? (int)$product['stock_quantity'] : 0;
    } catch (Exception $e) {
        error_log("Error getting actual stock: " . $e->getMessage());
        return 0;
    }
}

?>