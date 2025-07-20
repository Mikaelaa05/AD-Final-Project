<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';

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
            $successMessage = 'Stock quantity updated successfully!';
            break;
    }
}

if (isset($_GET['error'])) {
    $errorMessage = 'Error: ' . htmlspecialchars($_GET['error']);
}

$content = '
<div class="admin-container">
    <div class="admin-header">
        <h1>🛠️ Product Management</h1>
        <p>Manage your product inventory - Add, edit, update stock, or remove products</p>
        <div class="user-info">
            Welcome, <strong>' . htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']) . '</strong> 
            <span class="role-badge">' . htmlspecialchars($_SESSION['user']['role']) . '</span>
        </div>
    </div>';

if ($successMessage) {
    $content .= '<div class="success-message">✅ ' . $successMessage . '</div>';
}

if ($errorMessage) {
    $content .= '<div class="error-message">❌ ' . $errorMessage . '</div>';
}

$content .= '
    <div class="admin-actions">
        <button class="btn btn-primary" id="addProductBtn">
            ➕ Add New Product
        </button>
        <div class="search-filters">';../../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';

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

$content = '
<div class="admin-container">
    <div class="admin-header">
        <h1>🛠️ Product Management</h1>
        <p>Manage your product inventory - Add, edit, update stock, or remove products</p>
        <div class="user-info">
            Welcome, <strong>' . htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']) . '</strong> 
            <span class="role-badge">' . htmlspecialchars($_SESSION['user']['role']) . '</span>
        </div>
    </div>

    <div class="admin-actions">
        <button class="btn btn-primary" id="addProductBtn">
            ➕ Add New Product
        </button>
        <div class="search-filters">
            <input type="text" id="searchProducts" placeholder="🔍 Search products..." />
            <select id="categoryFilter">
                <option value="">All Categories</option>';

foreach ($categories as $category) {
    $content .= '<option value="' . htmlspecialchars($category) . '">' . htmlspecialchars($category) . '</option>';
}

$content .= '
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
            <tbody id="productsTableBody">';

foreach ($products as $product) {
    $statusClass = $product['is_active'] ? 'active' : 'inactive';
    $statusText = $product['is_active'] ? 'Active' : 'Inactive';

    $content .= '
                <tr data-product-id="' . htmlspecialchars($product['id']) . '">
                    <td class="product-info">
                        <div class="product-name">' . htmlspecialchars($product['name']) . '</div>
                        <div class="product-description">' . htmlspecialchars(substr($product['description'], 0, 60)) . '...</div>
                    </td>
                    <td class="sku">' . htmlspecialchars($product['sku']) . '</td>
                    <td class="category">' . htmlspecialchars($product['category']) . '</td>
                    <td class="price">$' . number_format((float)$product['price'], 2) . '</td>
                    <td class="stock">
                        <div class="stock-controls">
                            <!-- Decrease Stock Form -->
                            <form method="POST" action="/handlers/adminStock.handler.php" style="display: inline;">
                                <input type="hidden" name="action" value="decrease">
                                <input type="hidden" name="product_id" value="' . htmlspecialchars($product['id']) . '">
                                <button type="submit" class="stock-btn decrease">-</button>
                            </form>
                            
                            <span class="stock-count">' . $product['stock_quantity'] . '</span>
                            
                            <!-- Increase Stock Form -->
                            <form method="POST" action="/handlers/adminStock.handler.php" style="display: inline;">
                                <input type="hidden" name="action" value="increase">
                                <input type="hidden" name="product_id" value="' . htmlspecialchars($product['id']) . '">
                                <button type="submit" class="stock-btn increase">+</button>
                            </form>
                        </div>
                    </td>
                    <td class="status">
                        <span class="status-badge ' . $statusClass . '">' . $statusText . '</span>
                    </td>
                    <td class="actions">
                        <button class="btn btn-sm btn-edit" data-action="edit" data-id="' . htmlspecialchars($product['id']) . '">
                            ✏️ Edit
                        </button>
                        <button class="btn btn-sm btn-delete" data-action="delete" data-id="' . htmlspecialchars($product['id']) . '">
                            🗑️ Delete
                        </button>
                    </td>
                </tr>';
}

$content .= '
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Product Modal (Placeholder) -->
    <div class="modal" id="productModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Product</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <div class="form-group">
                        <label for="productName">Product Name</label>
                        <input type="text" id="productName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="productSku">SKU</label>
                        <input type="text" id="productSku" name="sku" required>
                    </div>
                    <div class="form-group">
                        <label for="productCategory">Category</label>
                        <select id="productCategory" name="category">
                            <option value="">Select Category</option>';

foreach ($categories as $category) {
    $content .= '<option value="' . htmlspecialchars($category) . '">' . htmlspecialchars($category) . '</option>';
}

$content .= '
                            <option value="custom">+ Add New Category</option>
                        </select>
                        <input type="text" id="customCategory" name="customCategory" placeholder="Enter new category" style="display: none;">
                    </div>
                    <div class="form-group">
                        <label for="productPrice">Price ($)</label>
                        <input type="number" id="productPrice" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="productCost">Cost ($)</label>
                        <input type="number" id="productCost" name="cost" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="productStock">Stock Quantity</label>
                        <input type="number" id="productStock" name="stock_quantity" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="productWeight">Weight (kg)</label>
                        <input type="number" id="productWeight" name="weight" step="0.1">
                    </div>
                    <div class="form-group">
                        <label for="productDescription">Description</label>
                        <textarea id="productDescription" name="description" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="productActive" name="is_active" checked>
                            Product is active
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveProductBtn">Save Product</button>
            </div>
        </div>
    </div>

    <!-- Statistics Panel -->
    <div class="stats-panel">
        <div class="stat-card">
            <div class="stat-number">' . count($products) . '</div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">' . count(array_filter($products, fn($p) => $p['is_active'])) . '</div>
            <div class="stat-label">Active Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">' . array_sum(array_column($products, 'stock_quantity')) . '</div>
            <div class="stat-label">Total Stock</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">' . count($categories) . '</div>
            <div class="stat-label">Categories</div>
        </div>
    </div>
</div>

<script>
// Placeholder JavaScript for button interactions
document.addEventListener("DOMContentLoaded", function() {
    // Add Product Button
    document.getElementById("addProductBtn").addEventListener("click", function() {
        alert("Add Product functionality will be implemented here");
        document.getElementById("productModal").style.display = "block";
    });

    // Edit Product Buttons
    document.querySelectorAll(".btn-edit").forEach(button => {
        button.addEventListener("click", function() {
            const productId = this.dataset.id;
            alert("Edit Product functionality will be implemented here for product ID: " + productId);
            document.getElementById("productModal").style.display = "block";
        });
    });

    // Delete Product Buttons
    document.querySelectorAll(".btn-delete").forEach(button => {
        button.addEventListener("click", function() {
            const productId = this.dataset.id;
            if (confirm("Are you sure you want to delete this product?")) {
                alert("Delete Product functionality will be implemented here for product ID: " + productId);
            }
        });
    });

    // Stock Increase/Decrease Buttons
    document.querySelectorAll(".stock-btn").forEach(button => {
        button.addEventListener("click", function() {
            const action = this.dataset.action;
            const productId = this.dataset.id;
            const stockElement = this.parentElement.querySelector(".stock-count");
            const currentStock = parseInt(stockElement.textContent);
            
            if (action === "increase") {
                alert("Increase stock functionality will be implemented here for product ID: " + productId);
                // stockElement.textContent = currentStock + 1;
            } else if (action === "decrease" && currentStock > 0) {
                alert("Decrease stock functionality will be implemented here for product ID: " + productId);
                // stockElement.textContent = currentStock - 1;
            }
        });
    });

    // Modal Controls
    document.getElementById("closeModal").addEventListener("click", function() {
        document.getElementById("productModal").style.display = "none";
    });

    document.getElementById("cancelBtn").addEventListener("click", function() {
        document.getElementById("productModal").style.display = "none";
    });

    document.getElementById("saveProductBtn").addEventListener("click", function() {
        alert("Save Product functionality will be implemented here");
        document.getElementById("productModal").style.display = "none";
    });

    // Category dropdown change
    document.getElementById("productCategory").addEventListener("change", function() {
        const customInput = document.getElementById("customCategory");
        if (this.value === "custom") {
            customInput.style.display = "block";
            customInput.required = true;
        } else {
            customInput.style.display = "none";
            customInput.required = false;
        }
    });

    // Search functionality (placeholder)
    document.getElementById("searchProducts").addEventListener("input", function() {
        const searchTerm = this.value.toLowerCase();
        // Search functionality will be implemented here
        console.log("Searching for:", searchTerm);
    });

    // Category filter (placeholder)
    document.getElementById("categoryFilter").addEventListener("change", function() {
        const selectedCategory = this.value;
        // Filter functionality will be implemented here
        console.log("Filtering by category:", selectedCategory);
    });
});
</script>
';

include LAYOUTS_PATH . '/main.layout.php';
?>