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

// Get all active products
$sql = "SELECT * FROM products WHERE is_active = true ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Note: Product images are now handled via CSS background images
// No need for image mapping array anymore

// Define the content for the layout
ob_start();
?>
<div class="shop-container">
    <div class="shop-header">
        <h1><span style="color: #ff0040;">SIN</span>THESIZE Marketplace</h1>
        <p>Cybernetic Enhancements • Digital Weaponry • Reality Hacking Tools</p>
    </div>

    <?php if (!empty($products)): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card" data-product-id="<?= $product['id'] ?>" data-product-name="<?= strtolower(str_replace([' ', '.', '-'], ['', '', ''], $product['name'])) ?>">
                    <!-- Product image: CSS for local, dynamic for web URLs -->
                    <?php if (!empty($product['image_url']) && filter_var($product['image_url'], FILTER_VALIDATE_URL)): ?>
                        <!-- Web URL image -->
                        <div class="product-image web-image">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>"
                                alt="<?= htmlspecialchars($product['image_alt_text'] ?? $product['name']) ?>" 
                                loading="lazy"
                                onerror="this.style.display='none'; this.parentElement.classList.add('image-error');">
                        </div>
                    <?php else: ?>
                        <!-- CSS-based local image -->
                        <div class="product-image css-image" data-product="<?= strtolower(str_replace([' ', '.', '-'], ['', '', ''], $product['name'])) ?>">
                            <!-- CSS will handle the background image -->
                        </div>
                    <?php endif; ?>

                    <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>

                    <?php if (!empty($product['category'])): ?>
                        <span class="product-category"><?= htmlspecialchars($product['category']) ?></span>
                    <?php endif; ?>

                    <?php if (!empty($product['description'])): ?>
                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                    <?php endif; ?>

                    <div class="product-details">
                        <?php if (!empty($product['sku'])): ?>
                            <div class="detail-item">
                                <span class="detail-label">SKU:</span>
                                <span><?= htmlspecialchars($product['sku']) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($product['weight'])): ?>
                            <div class="detail-item">
                                <span class="detail-label">Weight:</span>
                                <span><?= htmlspecialchars($product['weight']) ?>kg</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-price">$<?= number_format($product['price'], 2) ?></div>

                    <?php
                    $stockQuantity = (int) $product['stock_quantity'];
                    if ($stockQuantity > 20):
                        $stockClass = 'in-stock';
                        $stockText = 'In Stock (' . $stockQuantity . ' available)';
                    elseif ($stockQuantity > 0):
                        $stockClass = 'low-stock';
                        $stockText = 'Low Stock (' . $stockQuantity . ' remaining)';
                    else:
                        $stockClass = 'out-of-stock';
                        $stockText = 'Out of Stock';
                    endif;
                    ?>

                    <div class="product-stock <?= $stockClass ?>" data-stock="<?= $stockQuantity ?>">
                        <?= $stockText ?>
                    </div>

                    <div class="product-actions">
                        <?php if ($stockQuantity > 0): ?>
                            <?php 
                            $isInCart = isItemInCart($product['id']);
                            $cartQuantity = getItemQuantityInCart($product['id']);
                            ?>
                            <button class="btn btn-primary add-to-cart <?= $isInCart ? 'in-cart' : '' ?>" 
                                    data-product-id="<?= $product['id'] ?>">
                                <?= $isInCart ? "In Cart ($cartQuantity)" : 'Add to Cart' ?>
                            </button>
                            <a href="/pages/Cart" class="btn btn-secondary">View Cart</a>
                        <?php else: ?>
                            <button class="btn btn-primary" disabled>Out of Stock</button>
                            <a href="/pages/Cart" class="btn btn-secondary">View Cart</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-products">
            <h3>No Products Found</h3>
            <p>No products are currently available. Please check back later.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
?>
<!DOCTYPE html>
<html>

<head>
    <title>SINTHESIZE Marketplace - Cybernetic Catalog</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/shop.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include LAYOUTS_PATH . '/main.layout.php'; ?>

    <script>
        // Add to Cart functionality
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function () {
                    const productId = this.dataset.productId;
                    const originalText = this.textContent;
                    
                    // Disable button and show loading state
                    this.disabled = true;
                    this.textContent = 'Adding...';
                    
                    // Create form data
                    const formData = new FormData();
                    formData.append('action', 'add');
                    formData.append('product_id', productId);
                    formData.append('quantity', 1);
                    
                    // Send AJAX request
                    fetch('/handlers/cart.handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            this.textContent = '✓ Added!';
                            this.style.backgroundColor = '#00ff7f';
                            
                            // Update cart count in navbar if it exists
                            const cartCount = document.querySelector('.cart-count');
                            if (cartCount) {
                                cartCount.textContent = data.cart_count;
                            } else if (data.cart_count > 0) {
                                // Create cart count if it doesn't exist
                                const cartLink = document.querySelector('.cart-link');
                                if (cartLink) {
                                    const countSpan = document.createElement('span');
                                    countSpan.className = 'cart-count';
                                    countSpan.textContent = data.cart_count;
                                    cartLink.appendChild(countSpan);
                                }
                            }
                            
                            // Update stock display if available
                            if (data.new_stock !== undefined) {
                                updateProductStock(productId, data.new_stock);
                                broadcastStockUpdate(productId, data.new_stock);
                                
                                // Check if item is now out of stock
                                if (data.new_stock <= 0) {
                                    showNotification('Item added to cart. Product is now out of stock.', 'info');
                                    return; // Don't proceed with normal success flow
                                }
                            }
                            
                            // Show success notification
                            showNotification('Product added to cart successfully!', 'success');
                            
                            // Reset button after 2 seconds and update to show in-cart state
                            setTimeout(() => {
                                updateButtonToInCartState(productId, 1);
                                this.disabled = false;
                            }, 2000);
                        } else {
                            // Show error message
                            showNotification(data.message, 'error');
                            this.textContent = originalText;
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error adding to cart:', error);
                        showNotification('Failed to add product to cart. Please try again.', 'error');
                        this.textContent = originalText;
                        this.disabled = false;
                    });
                });
            });
        });
        
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
        
        // Update product stock display
        function updateProductStock(productId, newStock) {
            const productCard = document.querySelector(`[data-product-id="${productId}"]`);
            if (!productCard) return;
            
            // If newStock is 'reload_needed', fetch current stock from server
            if (newStock === 'reload_needed') {
                fetchCurrentStock(productId);
                return;
            }
            
            const stockElement = productCard.querySelector('.product-stock');
            const addButton = productCard.querySelector('.add-to-cart');
            
            if (stockElement) {
                // Update stock data attribute
                stockElement.setAttribute('data-stock', newStock);
                
                // Update stock display and styling
                if (newStock > 20) {
                    stockElement.textContent = `In Stock (${newStock} available)`;
                    stockElement.className = 'product-stock in-stock';
                } else if (newStock > 0) {
                    stockElement.textContent = `Low Stock (${newStock} remaining)`;
                    stockElement.className = 'product-stock low-stock';
                } else {
                    stockElement.textContent = 'Out of Stock';
                    stockElement.className = 'product-stock out-of-stock';
                }
            }
            
            // Update add to cart button
            if (addButton) {
                if (newStock <= 0) {
                    addButton.disabled = true;
                    addButton.textContent = 'Out of Stock';
                    addButton.classList.remove('in-cart');
                } else if (addButton.disabled && newStock > 0) {
                    addButton.disabled = false;
                    addButton.textContent = 'Add to Cart';
                    addButton.classList.remove('in-cart');
                }
            }
        }
        
        // Fetch current stock from server
        function fetchCurrentStock(productId) {
            fetch(`/handlers/cart.handler.php?action=get_stock&product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateProductStock(productId, data.stock_quantity);
                        broadcastStockUpdate(productId, data.stock_quantity);
                    }
                })
                .catch(error => {
                    console.error('Error fetching current stock:', error);
                });
        }
        
        // Update button to show in-cart state
        function updateButtonToInCartState(productId, cartQuantity) {
            const productCard = document.querySelector(`[data-product-id="${productId}"]`);
            if (!productCard) return;
            
            const addButton = productCard.querySelector('.add-to-cart');
            if (addButton) {
                addButton.textContent = `In Cart (${cartQuantity})`;
                addButton.classList.add('in-cart');
                addButton.style.backgroundColor = '#00ff7f';
                addButton.style.color = '#000';
            }
        }
        
        // Listen for storage events to update stock when cart changes on other tabs/pages
        window.addEventListener('storage', function(event) {
            if (event.key === 'cart_stock_update') {
                const stockUpdate = JSON.parse(event.newValue);
                updateProductStock(stockUpdate.productId, stockUpdate.newStock);
            }
        });
        
        // Function to broadcast stock updates to other tabs
        function broadcastStockUpdate(productId, newStock) {
            localStorage.setItem('cart_stock_update', JSON.stringify({
                productId: productId,
                newStock: newStock,
                timestamp: Date.now()
            }));
            // Remove the item immediately to allow multiple updates
            setTimeout(() => localStorage.removeItem('cart_stock_update'), 100);
        }
        
        // Add CSS for animations and button states
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
            .add-to-cart.in-cart {
                background-color: #00ff7f !important;
                color: #000 !important;
            }
            .add-to-cart:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>