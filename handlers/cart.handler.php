<?php

header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';
require_once UTILS_PATH . '/cart.util.php';
require_once UTILS_PATH . '/errorHandler.util.php';

session_start();

// Check if user is authenticated
if (!isset($_SESSION['user'])) {
    ErrorHandler::jsonError('User not authenticated', 401);
}

// Configuration from environment
$typeConfig = envSetter('postgresql');

// Get the action from the request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (empty($action)) {
    ErrorHandler::jsonError('Invalid action', 400);
}

// Database connection
try {
    $host = $typeConfig['pgHost'];
    $port = $typeConfig['pgPort'];
    $username = $typeConfig['pgUser'];
    $password = $typeConfig['pgPass'];
    $dbname = $typeConfig['pgDb'];

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    error_log("Cart handler database connection failed: " . $e->getMessage());
    ErrorHandler::jsonError('Database connection failed', 500);
}

switch ($action) {
    case 'add':
        addToCart($pdo);
        break;
    case 'update':
        updateCartQuantity($pdo);
        break;
    case 'remove':
        removeFromCart($pdo);
        break;
    case 'clear':
        clearCart($pdo);
        break;
    case 'get':
        getCartItems($pdo);
        break;
    case 'get_stock':
        getCurrentStock($pdo);
        break;
    default:
        ErrorHandler::jsonError('Invalid action', 400);
}

function addToCart($pdo) {
    $productId = $_POST['product_id'] ?? '';
    $quantity = (int) ($_POST['quantity'] ?? 1);
    
    if (empty($productId) || $quantity <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
        return;
    }
    
    // Start transaction for stock management
    $pdo->beginTransaction();
    
    try {
        // Validate product exists and is active with current stock
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = true FOR UPDATE");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found or inactive']);
            return;
        }
        
        // Initialize cart
        initializeCart();
        
        // Check if product already exists in cart
        $currentQuantityInCart = getItemQuantityInCart($productId);
        $totalRequestedQuantity = $currentQuantityInCart + $quantity;
        
        // Check stock availability (including what's already in cart)
        if ($product['stock_quantity'] < $quantity) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Insufficient stock. Only ' . $product['stock_quantity'] . ' items available'
            ]);
            return;
        }
        
        // Decrease stock in database
        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$quantity, $productId]);
        
        // Update or add item to cart
        $productFound = false;
        foreach ($_SESSION['cart'] as &$cartItem) {
            if ($cartItem['id'] == $productId) {
                $cartItem['quantity'] = $totalRequestedQuantity;
                $productFound = true;
                break;
            }
        }
        
        // If product not in cart, add it
        if (!$productFound) {
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'product_name' => $product['name'],
                'product_sku' => $product['sku'],
                'price' => (float) $product['price'],
                'quantity' => $quantity,
                'category' => $product['category'],
                'description' => $product['description'],
                'image' => basename($product['image_url'] ?? 'default.png')
            ];
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Log activity
        logCartActivity('add', $productId, $quantity);
        
        // Calculate cart totals
        $cartSummary = calculateCartTotals();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Product added to cart successfully',
            'cart_count' => count($_SESSION['cart']),
            'cart_summary' => $cartSummary,
            'new_stock' => $product['stock_quantity'] - $quantity
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error adding to cart: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
    }
}

function updateCartQuantity($pdo) {
    $productId = $_POST['product_id'] ?? '';
    $quantity = (int) ($_POST['quantity'] ?? 1);
    
    if (empty($productId) || $quantity < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
        return;
    }
    
    if (!isset($_SESSION['cart'])) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        return;
    }
    
    // Start transaction for stock management
    $pdo->beginTransaction();
    
    try {
        // Get current quantity in cart
        $currentQuantityInCart = getItemQuantityInCart($productId);
        
        if ($currentQuantityInCart == 0) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
            return;
        }
        
        // Calculate the difference
        $quantityDifference = $quantity - $currentQuantityInCart;
        
        // If quantity is 0, remove the item (restore all stock)
        if ($quantity == 0) {
            // Restore stock for removed item
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$currentQuantityInCart, $productId]);
            
            $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($productId) {
                return $item['id'] != $productId;
            });
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
            
            $pdo->commit();
            logCartActivity('remove', $productId, 0);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Item removed from cart',
                'cart_count' => count($_SESSION['cart']),
                'cart_summary' => calculateCartTotals()
            ]);
            return;
        }
        
        // Get current product stock
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? AND is_active = true FOR UPDATE");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found or inactive']);
            return;
        }
        
        // Check if we have enough stock for the increase
        if ($quantityDifference > 0 && $product['stock_quantity'] < $quantityDifference) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Insufficient stock. Only ' . $product['stock_quantity'] . ' additional items available'
            ]);
            return;
        }
        
        // Update stock (decrease if increasing quantity, increase if decreasing quantity)
        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$quantityDifference, $productId]);
        
        // Update quantity in cart
        foreach ($_SESSION['cart'] as &$cartItem) {
            if ($cartItem['id'] == $productId) {
                $cartItem['quantity'] = $quantity;
                break;
            }
        }
        
        $pdo->commit();
        logCartActivity('update', $productId, $quantity);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Cart updated successfully',
            'cart_count' => count($_SESSION['cart']),
            'cart_summary' => calculateCartTotals(),
            'product_id' => $productId,
            'new_stock' => $product['stock_quantity'] + $quantityDifference
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating cart: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
    }
}

function removeFromCart($pdo) {
    $productId = $_POST['product_id'] ?? '';
    
    if (empty($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        return;
    }
    
    if (!isset($_SESSION['cart'])) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        return;
    }
    
    // Start transaction for stock management
    $pdo->beginTransaction();
    
    try {
        // Get the quantity to restore to stock
        $quantityToRestore = getItemQuantityInCart($productId);
        
        if ($quantityToRestore == 0) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
            return;
        }
        
        // Restore stock in database
        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$quantityToRestore, $productId]);
        
        // Get updated stock quantity
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $updatedProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Remove item from cart
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($productId) {
            return $item['id'] != $productId;
        });
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
        
        $pdo->commit();
        logCartActivity('remove', $productId);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Item removed from cart successfully',
            'cart_count' => count($_SESSION['cart']),
            'cart_summary' => calculateCartTotals(),
            'product_id' => $productId,
            'new_stock' => $updatedProduct ? (int)$updatedProduct['stock_quantity'] : 'reload_needed'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error removing from cart: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to remove item from cart']);
    }
}

function clearCart($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode([
            'success' => true, 
            'message' => 'Cart is already empty',
            'cart_count' => 0,
            'cart_summary' => calculateCartTotals()
        ]);
        return;
    }
    
    // Start transaction for stock management
    $pdo->beginTransaction();
    
    try {
        // Restore stock for all items in cart
        foreach ($_SESSION['cart'] as $cartItem) {
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$cartItem['quantity'], $cartItem['id']]);
        }
        
        $itemCount = getCartCount();
        $_SESSION['cart'] = [];
        
        $pdo->commit();
        logCartActivity('clear', null, $itemCount);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Cart cleared successfully',
            'cart_count' => 0,
            'cart_summary' => calculateCartTotals()
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error clearing cart: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
    }
}

function getCartItems($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode([
            'success' => true, 
            'cart_items' => [],
            'cart_count' => 0,
            'cart_summary' => calculateCartTotals()
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true, 
        'cart_items' => $_SESSION['cart'],
        'cart_count' => count($_SESSION['cart']),
        'cart_summary' => calculateCartTotals()
    ]);
}

function calculateCartTotals() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'total' => 0,
            'item_count' => 0
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
        'subtotal' => number_format($subtotal, 2),
        'tax' => number_format($tax, 2),
        'shipping' => $shipping == 0 ? 'FREE' : number_format($shipping, 2),
        'total' => number_format($total, 2),
        'item_count' => $itemCount,
        'free_shipping_threshold' => 200,
        'amount_for_free_shipping' => max(0, 200 - $subtotal)
    ];
}

function getCurrentStock($pdo) {
    $productId = $_GET['product_id'] ?? $_POST['product_id'] ?? '';
    
    if (empty($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? AND is_active = true");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'product_id' => $productId,
            'stock_quantity' => (int)$product['stock_quantity']
        ]);
        
    } catch (Exception $e) {
        error_log("Error getting current stock: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to get stock information']);
    }
}

?>