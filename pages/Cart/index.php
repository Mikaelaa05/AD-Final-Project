<?php
/**
 * Cart Page - Display user's shopping cart
 * Pure PHP forms - No AJAX
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/cart.util.php';

session_start();

// Check authentication
if (!isAuthenticated()) {
    header('Location: /pages/Login');
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update':
            $productId = $_POST['product_id'] ?? '';
            $quantity = (int)($_POST['quantity'] ?? 0);
            
            if ($productId && $quantity >= 0) {
                updateCartItem($userId, $productId, $quantity);
                header('Location: /pages/Cart');
                exit;
            }
            break;
            
        case 'remove':
            $productId = $_POST['product_id'] ?? '';
            if ($productId) {
                removeFromCart($userId, $productId);
                header('Location: /pages/Cart');
                exit;
            }
            break;
            
        case 'checkout':
            try {
                $orderId = checkoutCart($userId);
                header('Location: /pages/Cart?success=checkout&order=' . $orderId);
                exit;
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
            break;
    }
}

// Get cart items
$cartItems = getCartItems($userId);
$cartTotal = getCartTotal($userId);
$cartCount = getCartItemCount($userId);

// Page configuration
$pageTitle = 'Shopping Cart';
$pageCSS = '<link rel="stylesheet" href="/assets/css/cart.css">';

// Success message
$successMessage = '';
if (isset($_GET['success']) && $_GET['success'] === 'checkout') {
    $orderId = $_GET['order'] ?? '';
    $successMessage = "Order #{$orderId} placed successfully! Stock has been updated.";
}

// Start output buffering for content
ob_start();
?>

<div class="cart-container">
    <div class="cart-header">
        <h1>🛒 Your Shopping Cart</h1>
        <p>Review your items before checkout</p>
    </div>

    <?php if ($successMessage): ?>
        <div class="success-message">
            ✅ <?= htmlspecialchars($successMessage) ?>
            <a href="/pages/Shop" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="error-message">
            ❌ <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <h2>Your cart is empty</h2>
            <p>Add some products to get started!</p>
            <a href="/pages/Shop" class="btn btn-primary">Go Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <div class="item-info">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="item-description"><?= htmlspecialchars($item['description']) ?></p>
                        <p class="item-price">$<?= number_format((float)$item['price'], 2) ?> each</p>
                        <p class="item-stock">Stock available: <?= $item['stock_quantity'] ?></p>
                    </div>
                    
                    <div class="item-controls">
                        <!-- Update Quantity Form -->
                        <form method="POST" class="quantity-form">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['product_id']) ?>">
                            <label for="qty_<?= $item['product_id'] ?>">Quantity:</label>
                            <input type="number" 
                                   id="qty_<?= $item['product_id'] ?>" 
                                   name="quantity" 
                                   value="<?= $item['quantity'] ?>" 
                                   min="0" 
                                   max="<?= $item['stock_quantity'] ?>"
                                   onchange="this.form.submit()">
                        </form>
                        
                        <!-- Remove Item Form -->
                        <form method="POST" class="remove-form">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['product_id']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item from cart?')">
                                🗑️ Remove
                            </button>
                        </form>
                    </div>
                    
                    <div class="item-total">
                        <strong>$<?= number_format((float)$item['subtotal'], 2) ?></strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <div class="summary-row">
                <span>Total Items:</span>
                <span><?= $cartCount ?></span>
            </div>
            <div class="summary-row total-row">
                <span><strong>Total Amount:</strong></span>
                <span><strong>$<?= number_format((float)$cartTotal, 2) ?></strong></span>
            </div>
            
            <div class="cart-actions">
                <a href="/pages/Shop" class="btn btn-secondary">Continue Shopping</a>
                
                <!-- Checkout Form -->
                <form method="POST" class="checkout-form">
                    <input type="hidden" name="action" value="checkout">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Complete your order? This will update stock quantities.')">
                        💳 Checkout Now
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include LAYOUTS_PATH . '/main.layout.php';
?>
