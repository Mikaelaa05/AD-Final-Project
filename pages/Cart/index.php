<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';

session_start();

if (!isset($_SESSION['user'])) {
    include ERRORS_PATH . '/unauthorized.error.php';
    exit;
}

// Sample cart data for UI demonstration (backend will replace this)
$sampleCartItems = [
    [
        'id' => 1,
        'product_name' => 'IONFUEL VIALS',
        'product_sku' => 'ION-FUEL-001',
        'price' => 89.99,
        'quantity' => 2,
        'category' => 'Enhancement',
        'description' => 'High-performance energy enhancement vials for extended neural activity.',
        'image' => 'IonFuelVials.png'
    ],
    [
        'id' => 2,
        'product_name' => 'NEUROSPARK NODE',
        'product_sku' => 'NEURO-NODE-003',
        'price' => 299.99,
        'quantity' => 1,
        'category' => 'Cybernetic',
        'description' => 'Advanced neural processing node for enhanced cognitive functions.',
        'image' => 'NeuroSparkNode.png'
    ],
    [
        'id' => 3,
        'product_name' => 'SYNTHCELL BATTERY PACK',
        'product_sku' => 'SYNTH-BAT-005',
        'price' => 149.99,
        'quantity' => 1,
        'category' => 'Power',
        'description' => 'High-capacity synthetic power cells for extended operation.',
        'image' => 'SynthCellBatteryPack.png'
    ]
];

// Calculate totals
$subtotal = 0;
foreach ($sampleCartItems as $item) {
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

    <?php if (!empty($sampleCartItems)): ?>
        <div class="cart-content">
            <!-- Cart Items Section -->
            <div class="cart-items-section">
                <div class="cart-items-header">
                    <h2>🛒 Cart Items (<?= count($sampleCartItems) ?>)</h2>
                    <button class="btn btn-secondary clear-cart" id="clearCartBtn">
                        🗑️ Clear Cart
                    </button>
                </div>

                <div class="cart-items">
                    <?php foreach ($sampleCartItems as $item): ?>
                        <div class="cart-item" data-item-id="<?= $item['id'] ?>">
                            <div class="item-image">
                                <img src="/pages/Shop/assets/img/<?= $item['image'] ?>"
                                    alt="<?= htmlspecialchars($item['product_name']) ?>" onerror="this.style.display='none'">
                            </div>

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
    // Placeholder JavaScript for cart interactions
    document.addEventListener('DOMContentLoaded', function () {
        // Quantity Controls
        document.querySelectorAll('.qty-btn').forEach(button => {
            button.addEventListener('click', function () {
                const action = this.dataset.action;
                const itemId = this.dataset.id;
                const input = this.parentElement.querySelector('.qty-input');
                let currentQty = parseInt(input.value);

                if (action === 'increase' && currentQty < 99) {
                    input.value = currentQty + 1;
                    console.log(`Increase quantity for item ${itemId} to ${currentQty + 1}`);
                    // Backend will implement: updateCartQuantity(itemId, currentQty + 1)
                } else if (action === 'decrease' && currentQty > 1) {
                    input.value = currentQty - 1;
                    console.log(`Decrease quantity for item ${itemId} to ${currentQty - 1}`);
                    // Backend will implement: updateCartQuantity(itemId, currentQty - 1)
                }

                // Placeholder: Update totals (backend will handle this)
                updateCartTotals();
            });
        });

        // Remove Item
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function () {
                const itemId = this.dataset.id;
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    console.log(`Remove item ${itemId} from cart`);
                    // Backend will implement: removeFromCart(itemId)
                    alert('Remove item functionality will be implemented by backend');
                }
            });
        });

        // Edit Item
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function () {
                const itemId = this.dataset.id;
                console.log(`Edit item ${itemId}`);
                // Backend will implement: editCartItem(itemId)
                alert('Edit item functionality will be implemented by backend');
            });
        });

        // Clear Cart
        document.getElementById('clearCartBtn')?.addEventListener('click', function () {
            if (confirm('Are you sure you want to clear your entire cart?')) {
                console.log('Clear entire cart');
                // Backend will implement: clearCart()
                alert('Clear cart functionality will be implemented by backend');
            }
        });

        // Checkout
        document.getElementById('checkoutBtn')?.addEventListener('click', function () {
            console.log('Proceed to checkout');
            // Backend will implement: proceedToCheckout()
            alert('Checkout functionality will be implemented by backend');
        });

        // Placeholder function to update totals
        function updateCartTotals() {
            console.log('Update cart totals - backend will implement this');
            // Backend will recalculate and update all totals
        }
    });
</script>

<?php
$content = ob_get_clean();

// Include the layout
include LAYOUTS_PATH . '/main.layout.php';
?>