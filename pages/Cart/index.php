<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';
require_once UTILS_PATH . '/cart.util.php';

session_start();

if (!isset($_SESSION['user'])) {
    include ERRORS_PATH . '/unauthorized.error.php';
    exit;
}

// Debug: Log user session data
$user = $_SESSION['user'];
error_log("Cart page - User session data: " . json_encode($user));

// Get cart items from session
$cartItems = $_SESSION['cart'] ?? [];

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * 0.08; // 8% tax
$shipping = $subtotal > 200 ? 0 : 15.99; // Free shipping over $200
$total = $subtotal + $tax + $shipping;

// Page configuration
$pageTitle = 'Shopping Cart';
$pageCSS = '<link rel="stylesheet" href="/assets/css/cart.css?v=' . time() . '">';

// Define the content for the layout
ob_start();
?>
<div class="cart-container">
    <div class="cart-header">
        <h1><span style="color: #ff0040;">SIN</span>THESIZE Cart</h1>
        <p>Review your cybernetic selections before checkout</p>
    </div>

    <?php if (!empty($cartItems)): ?>
        <div class="cart-content">
            <!-- Cart Items Section -->
            <div class="cart-items-section">
                <div class="cart-items-header">
                    <h2>🛒 Cart Items (<?= count($cartItems) ?>)</h2>
                    <button class="btn btn-secondary clear-cart" id="clearCartBtn">
                        🗑️ Clear Cart
                    </button>
                </div>

                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-item-id="<?= $item['id'] ?>">
                            <div class="item-details">
                                <h3 class="item-name"><?= htmlspecialchars($item['product_name']) ?></h3>
                                <p class="item-sku">SKU: <?= htmlspecialchars($item['product_sku']) ?></p>
                                <p class="item-category"><?= htmlspecialchars($item['category']) ?></p>
                                <p class="item-description"><?= htmlspecialchars($item['description']) ?></p>
                            </div>

                            <div class="item-price">
                                <span class="price-label">Unit Price:</span>
                                <span class="unit-price">$<?= number_format($item['price'], 2) ?></span>
                            </div>

                            <div class="item-quantity">
                                <label>Quantity:</label>
                                <div class="quantity-controls">
                                    <button class="qty-btn decrease" data-action="decrease"
                                        data-id="<?= $item['id'] ?>">-</button>
                                    <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" max="99"
                                        readonly>
                                    <button class="qty-btn increase" data-action="increase"
                                        data-id="<?= $item['id'] ?>">+</button>
                                </div>
                            </div>

                            <div class="item-total">
                                <span class="total-label">Total:</span>
                                <span
                                    class="item-total-price">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>

                            <div class="item-actions">
                                <button class="btn btn-edit" data-action="edit" data-id="<?= $item['id'] ?>">
                                    ✏️ Edit
                                </button>
                                <button class="btn btn-delete" data-action="remove" data-id="<?= $item['id'] ?>">
                                    ❌ Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Cart Summary Section -->
            <div class="cart-summary-section">
                <div class="cart-summary">
                    <h2>📋 Order Summary</h2>

                    <div class="summary-line">
                        <span>Subtotal:</span>
                        <span class="subtotal">$<?= number_format($subtotal, 2) ?></span>
                    </div>

                    <div class="summary-line">
                        <span>Tax (8%):</span>
                        <span class="tax">$<?= number_format($tax, 2) ?></span>
                    </div>

                    <div class="summary-line">
                        <span>Shipping:</span>
                        <span class="shipping <?= $shipping == 0 ? 'free' : '' ?>">
                            <?= $shipping == 0 ? 'FREE' : '$' . number_format($shipping, 2) ?>
                        </span>
                    </div>

                    <?php if ($subtotal < 200 && $shipping > 0): ?>
                        <div class="shipping-notice">
                            💡 Add $<?= number_format(200 - $subtotal, 2) ?> more for free shipping!
                        </div>
                    <?php endif; ?>

                    <hr class="summary-divider">

                    <div class="summary-line total-line">
                        <span>Total:</span>
                        <span class="total">$<?= number_format($total, 2) ?></span>
                    </div>

                    <div class="cart-actions">
                        <button class="btn btn-primary checkout-btn" id="checkoutBtn">
                            🚀 Proceed to Checkout
                        </button>
                        <a href="/pages/Shop" class="btn btn-secondary continue-shopping">
                            🔄 Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Empty Cart -->
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <h2>Your Cart is Empty</h2>
            <p>Looks like you haven't added any cybernetic enhancements to your cart yet.</p>
            <a href="/pages/Shop" class="btn btn-primary">
                🔍 Browse Products
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
    // Cart interactions with AJAX
    document.addEventListener('DOMContentLoaded', function () {
        // Quantity Controls
        document.querySelectorAll('.qty-btn').forEach(button => {
            button.addEventListener('click', function () {
                const action = this.dataset.action;
                const itemId = this.dataset.id;
                const input = this.parentElement.querySelector('.qty-input');
                let currentQty = parseInt(input.value);
                let newQty = currentQty;

                if (action === 'increase' && currentQty < 99) {
                    newQty = currentQty + 1;
                } else if (action === 'decrease' && currentQty > 1) {
                    newQty = currentQty - 1;
                } else {
                    return; // No change needed
                }

                updateCartQuantity(itemId, newQty, input);
            });
        });

        // Remove Item
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function () {
                const itemId = this.dataset.id;
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    removeFromCart(itemId);
                }
            });
        });

        // Edit Item (placeholder - could open a modal or redirect)
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function () {
                const itemId = this.dataset.id;
                showNotification('Edit functionality coming soon!', 'info');
            });
        });

        // Clear Cart
        document.getElementById('clearCartBtn')?.addEventListener('click', function () {
            if (confirm('Are you sure you want to clear your entire cart?')) {
                clearCart();
            }
        });

        // Checkout
        document.getElementById('checkoutBtn')?.addEventListener('click', function () {
            const button = this;
            const originalText = button.innerHTML;
            
            // Disable button and show loading state
            button.disabled = true;
            button.innerHTML = '⏳ Processing...';
            
            fetch('/handlers/checkout.handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=checkout'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Checkout response:', data);
                if (data.success) {
                    // Show success popup
                    showOrderSuccessPopup(data.order_number, data.total_amount);
                    
                    // Clear cart display after short delay
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                } else {
                    showNotification(data.message || 'Order processing failed', 'error');
                }
            })
            .catch(error => {
                console.error('Checkout error:', error);
                showNotification('An error occurred during checkout: ' + error.message, 'error');
            })
            .finally(() => {
                // Re-enable button
                button.disabled = false;
                button.innerHTML = originalText;
            });
        });

        // Update cart quantity
        function updateCartQuantity(productId, quantity, inputElement) {
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            fetch('/handlers/cart.handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    inputElement.value = quantity;
                    updateCartTotals(data.cart_summary);
                    showNotification('Cart updated successfully!', 'success');
                    
                    // Broadcast stock update if available
                    if (data.product_id && data.new_stock !== undefined) {
                        broadcastStockUpdate(data.product_id, data.new_stock);
                    }
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error updating cart:', error);
                showNotification('Failed to update cart. Please refresh the page.', 'error');
            });
        }

        // Remove from cart
        function removeFromCart(productId) {
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('product_id', productId);

            fetch('/handlers/cart.handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the item from the DOM
                    const cartItem = document.querySelector(`[data-item-id="${productId}"]`);
                    if (cartItem) {
                        cartItem.remove();
                    }
                    
                    // Check if cart is now empty
                    const remainingItems = document.querySelectorAll('.cart-item');
                    if (remainingItems.length === 0) {
                        location.reload(); // Reload to show empty cart message
                    } else {
                        updateCartTotals(data.cart_summary);
                        updateCartCount(data.cart_count);
                    }
                    
                    // Broadcast stock update if available
                    if (data.product_id && data.new_stock !== undefined) {
                        broadcastStockUpdate(data.product_id, data.new_stock);
                    }
                    
                    showNotification('Item removed from cart!', 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error removing from cart:', error);
                showNotification('Failed to remove item. Please refresh the page.', 'error');
            });
        }

        // Clear cart
        function clearCart() {
            const formData = new FormData();
            formData.append('action', 'clear');

            fetch('/handlers/cart.handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Reload to show empty cart
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error clearing cart:', error);
                showNotification('Failed to clear cart. Please refresh the page.', 'error');
            });
        }

        // Update cart totals on the page
        function updateCartTotals(cartSummary) {
            const subtotalElement = document.querySelector('.subtotal');
            const taxElement = document.querySelector('.tax');
            const shippingElement = document.querySelector('.shipping');
            const totalElement = document.querySelector('.total');

            if (subtotalElement) subtotalElement.textContent = '$' + cartSummary.subtotal;
            if (taxElement) taxElement.textContent = '$' + cartSummary.tax;
            if (shippingElement) {
                shippingElement.textContent = cartSummary.shipping === 'FREE' ? 'FREE' : '$' + cartSummary.shipping;
                shippingElement.className = cartSummary.shipping === 'FREE' ? 'shipping free' : 'shipping';
            }
            if (totalElement) totalElement.textContent = '$' + cartSummary.total;

            // Update shipping notice
            const shippingNotice = document.querySelector('.shipping-notice');
            if (shippingNotice && cartSummary.amount_for_free_shipping > 0) {
                shippingNotice.innerHTML = `💡 Add $${cartSummary.amount_for_free_shipping.toFixed(2)} more for free shipping!`;
            } else if (shippingNotice) {
                shippingNotice.style.display = 'none';
            }
        }

        // Update cart count in header
        function updateCartCount(count) {
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = count;
            }
            
            // Update cart items header
            const cartItemsHeader = document.querySelector('.cart-items-section h2');
            if (cartItemsHeader) {
                cartItemsHeader.textContent = `🛒 Cart Items (${count})`;
            }
        }

        // Notification function
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());
            
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: bold;
                z-index: 10000;
                min-width: 300px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                animation: slideIn 0.3s ease-out;
            `;
            
            // Set background color based on type
            if (type === 'success') {
                notification.style.backgroundColor = '#00ff7f';
                notification.style.color = '#000';
            } else if (type === 'error') {
                notification.style.backgroundColor = '#ff0040';
            } else {
                notification.style.backgroundColor = '#00ffff';
                notification.style.color = '#000';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            // Auto-remove notification after 5 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }

        // Function to show order success popup
        function showOrderSuccessPopup(orderNumber, totalAmount) {
            const popup = document.createElement('div');
            popup.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
                animation: fadeIn 0.3s ease-out;
            `;
            
            const modal = document.createElement('div');
            modal.style.cssText = `
                background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
                border: 2px solid #00ff7f;
                border-radius: 15px;
                padding: 40px;
                text-align: center;
                color: white;
                max-width: 500px;
                width: 90%;
                box-shadow: 0 20px 40px rgba(0, 255, 127, 0.2);
                animation: slideInUp 0.5s ease-out;
            `;
            
            modal.innerHTML = `
                <div style="font-size: 4rem; margin-bottom: 20px;">🎉</div>
                <h2 style="color: #00ff7f; margin-bottom: 15px; font-size: 2rem;">Order Successful!</h2>
                <p style="margin-bottom: 10px; font-size: 1.1rem;">Your order has been placed successfully.</p>
                <div style="background: rgba(0, 255, 127, 0.1); padding: 20px; border-radius: 10px; margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong>Order Number:</strong> <span style="color: #00ff7f;">${orderNumber}</span></p>
                    <p style="margin: 5px 0;"><strong>Total Amount:</strong> <span style="color: #00ff7f;">$${totalAmount}</span></p>
                </div>
                <p style="margin-bottom: 25px; color: #ccc;">You will receive a confirmation shortly.</p>
                <button id="closeSuccessPopup" style="
                    background: #00ff7f;
                    color: #000;
                    border: none;
                    padding: 12px 30px;
                    border-radius: 8px;
                    font-weight: bold;
                    font-size: 1rem;
                    cursor: pointer;
                    transition: all 0.3s ease;
                ">Continue Shopping</button>
            `;
            
            popup.appendChild(modal);
            document.body.appendChild(popup);
            
            // Close popup functionality
            document.getElementById('closeSuccessPopup').addEventListener('click', () => {
                popup.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => popup.remove(), 300);
                window.location.href = '/pages/Shop';
            });
            
            // Auto-close after 10 seconds
            setTimeout(() => {
                if (popup.parentNode) {
                    popup.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => popup.remove(), 300);
                }
            }, 10000);
        }

        // Function to broadcast stock updates to other tabs
        function broadcastStockUpdate(productId, newStock) {
            if (newStock === 'reload_needed') return; // Don't broadcast reload signals
            
            localStorage.setItem('cart_stock_update', JSON.stringify({
                productId: productId,
                newStock: newStock,
                timestamp: Date.now()
            }));
            // Remove the item immediately to allow multiple updates
            setTimeout(() => localStorage.removeItem('cart_stock_update'), 100);
        }
        
        // Add CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            @keyframes slideInUp {
                from { transform: translateY(50px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    });
</script>

<?php
$content = ob_get_clean();

// Include the layout
include LAYOUTS_PATH . '/main.layout.php';
?>