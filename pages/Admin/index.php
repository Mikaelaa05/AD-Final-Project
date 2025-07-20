<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';

session_start();

// Check if user is authenticated and is an admin
if (!isAuthenticated()) {
    header('Location: /pages/Login');
    exit;
}

if (!isAdmin()) {
    header('Location: /errors/unauthorized.error.php');
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

// Fetch all products
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get product categories for the dropdown
$categorySql = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category";
$categoryStmt = $pdo->query($categorySql);
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

// Page configuration
$pageTitle = 'Admin - Product Management';
$pageCSS = '<link rel="stylesheet" href="/assets/css/admin.css">';

// Check for success/error messages
$successMessage = '';
$errorMessage = '';

if (isset($_GET['stock_updated'])) {
    switch ($_GET['stock_updated']) {
        case 'increased':
            $successMessage = 'Stock increased successfully!';
            break;
        case 'decreased':
            $successMessage = 'Stock decreased successfully!';
            break;
        case 'set':
            $successMessage = 'Stock quantity updated successfully!';
            break;
    }
}

if (isset($_GET['error'])) {
    $errorMessage = 'Error: ' . htmlspecialchars($_GET['error']);
}

// Start output buffering for content
ob_start();
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>🛠️ Product Management</h1>
        <p>Manage your product inventory - Add, edit, update stock, or remove products</p>
        <div class="user-info">
            Welcome, <strong><?= htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']) ?></strong> 
            <span class="role-badge"><?= htmlspecialchars($_SESSION['user']['role']) ?></span>
        </div>
    </div>

    <?php if ($successMessage): ?>
        <div class="success-message">✅ <?= $successMessage ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="error-message">❌ <?= $errorMessage ?></div>
    <?php endif; ?>

    <div class="admin-actions">
        <button class="btn btn-primary" id="addProductBtn">
            ➕ Add New Product
        </button>
        <div class="search-filters">
            <input type="text" id="searchProducts" placeholder="🔍 Search products..." />
            <select id="categoryFilter">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="products-table-container">
        <table class="products-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="productsTableBody">
                <?php foreach ($products as $product): ?>
                    <?php
                    $statusClass = $product['is_active'] ? 'active' : 'inactive';
                    $statusText = $product['is_active'] ? 'Active' : 'Inactive';
                    ?>
                    <tr data-product-id="<?= htmlspecialchars($product['id']) ?>">
                        <td class="product-info">
                            <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                            <div class="product-description"><?= htmlspecialchars(substr($product['description'], 0, 60)) ?>...</div>
                        </td>
                        <td class="sku"><?= htmlspecialchars($product['sku']) ?></td>
                        <td class="category"><?= htmlspecialchars($product['category']) ?></td>
                        <td class="price">$<?= number_format((float)$product['price'], 2) ?></td>
                        <td class="stock">
                            <div class="stock-controls">
                                <!-- Decrease Stock Form -->
                                <form method="POST" action="/handlers/adminStock.handler.php" style="display: inline;">
                                    <input type="hidden" name="action" value="decrease">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                                    <button type="submit" class="stock-btn decrease">-</button>
                                </form>
                                
                                <span class="stock-count"><?= $product['stock_quantity'] ?></span>
                                
                                <!-- Increase Stock Form -->
                                <form method="POST" action="/handlers/adminStock.handler.php" style="display: inline;">
                                    <input type="hidden" name="action" value="increase">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                                    <button type="submit" class="stock-btn increase">+</button>
                                </form>
                            </div>
                        </td>
                        <td class="status">
                            <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                        </td>
                        <td class="actions">
                            <button class="btn btn-sm btn-edit" data-action="edit" data-id="<?= htmlspecialchars($product['id']) ?>">
                                ✏️ Edit
                            </button>
                            <button class="btn btn-sm btn-delete" data-action="delete" data-id="<?= htmlspecialchars($product['id']) ?>">
                                🗑️ Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Statistics Panel -->
    <div class="stats-panel">
        <div class="stat-card">
            <div class="stat-number"><?= count($products) ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count(array_filter($products, fn($p) => $p['is_active'])) ?></div>
            <div class="stat-label">Active Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= array_sum(array_column($products, 'stock_quantity')) ?></div>
            <div class="stat-label">Total Stock</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count($categories) ?></div>
            <div class="stat-label">Categories</div>
        </div>
    </div>
</div>

<script>
// Simple placeholder JavaScript
document.addEventListener("DOMContentLoaded", function() {
    // Add Product Button (placeholder)
    const addBtn = document.getElementById("addProductBtn");
    if (addBtn) {
        addBtn.addEventListener("click", function() {
            alert("Add Product functionality - coming soon!");
        });
    }

    // Edit/Delete buttons (placeholder)
    document.querySelectorAll(".btn-edit").forEach(button => {
        button.addEventListener("click", function() {
            const productId = this.dataset.id;
            alert("Edit Product functionality - coming soon!\nProduct ID: " + productId);
        });
    });

    document.querySelectorAll(".btn-delete").forEach(button => {
        button.addEventListener("click", function() {
            const productId = this.dataset.id;
            if (confirm("Are you sure you want to delete this product?")) {
                alert("Delete Product functionality - coming soon!\nProduct ID: " + productId);
            }
        });
    });

    // Search functionality (placeholder)
    const searchInput = document.getElementById("searchProducts");
    if (searchInput) {
        searchInput.addEventListener("input", function() {
            console.log("Searching for:", this.value);
            // Search functionality will be implemented here
        });
    }

    // Category filter (placeholder)
    const categoryFilter = document.getElementById("categoryFilter");
    if (categoryFilter) {
        categoryFilter.addEventListener("change", function() {
            console.log("Filtering by category:", this.value);
            // Filter functionality will be implemented here
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include LAYOUTS_PATH . '/main.layout.php';
?>
