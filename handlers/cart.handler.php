<?php
declare(strict_types=1);
/**
 * Cart Handler - Handle PHP form cart operations (No AJAX)
 */

require_once __DIR__ . '/../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/cart.util.php';

session_start();

// Check if user is authenticated
if (!isAuthenticated()) {
    header('Location: /pages/Login');
    exit;
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/Shop');
    exit;
}

$userId = $_SESSION['user']['id'];
$action = $_POST['action'] ?? '';
$redirectUrl = $_POST['redirect_url'] ?? '/pages/Shop';

try {
    switch ($action) {
        case 'add':
            $productId = $_POST['product_id'] ?? '';
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if (empty($productId) || $quantity <= 0) {
                throw new Exception('Invalid product or quantity');
            }
            
            $success = addToCart($userId, $productId, $quantity);
            
            if ($success) {
                header('Location: ' . $redirectUrl . '?added=1');
            } else {
                header('Location: ' . $redirectUrl . '?error=add_failed');
            }
            break;
            
        case 'update':
            $productId = $_POST['product_id'] ?? '';
            $quantity = (int)($_POST['quantity'] ?? 0);
            
            if (empty($productId)) {
                throw new Exception('Invalid product ID');
            }
            
            $success = updateCartItem($userId, $productId, $quantity);
            header('Location: /pages/Cart');
            break;
            
        case 'remove':
            $productId = $_POST['product_id'] ?? '';
            
            if (empty($productId)) {
                throw new Exception('Invalid product ID');
            }
            
            $success = removeFromCart($userId, $productId);
            header('Location: /pages/Cart');
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log("Cart handler error: " . $e->getMessage());
    header('Location: ' . $redirectUrl . '?error=' . urlencode($e->getMessage()));
}

exit;
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $shippingInfo = [
                'address' => $_POST['shipping_address'] ?? '',
                'city' => $_POST['shipping_city'] ?? '',
                'state' => $_POST['shipping_state'] ?? '',
                'zip' => $_POST['shipping_zip'] ?? '',
                'country' => $_POST['shipping_country'] ?? 'USA'
            ];
            
            $result = checkoutCart($userId, $shippingInfo);
            
            echo json_encode($result);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
