<?php
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

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Fetch all products
    $sql = "SELECT * FROM products ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Admin page database error: " . $e->getMessage());
    $products = [];
    $dbError = "Database connection failed. Please try again later.";
}

// Get user info for welcome message
$user = $_SESSION['user'] ?? [];
$firstName = $user['first_name'] ?? '';
$lastName = $user['last_name'] ?? '';
$role = $user['role'] ?? 'Admin';
$displayName = trim($firstName . ' ' . $lastName) ?: ($user['username'] ?? 'Admin User');

// Get product categories for filter dropdown
$categories = [];
try {
    $categorySql = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category";
    $categoryStmt = $pdo->query($categorySql);
    $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Categories will remain empty if query fails
}

// Page configuration
$pageTitle = 'Admin - Product Management';
$pageCSS = '<link rel="stylesheet" href="/assets/css/admin.css">';

$content = '
<div class="admin-container">
    <!-- Header Section -->
    <div class="admin-header">
        <div class="header-content">
            <div class="header-left">
                <h1><span class="tool-icon">🛠️</span> Product Management</h1>
                <p class="header-subtitle">Manage your product inventory - Add, edit, update stock, or remove products</p>
            </div>
            <div class="header-right">
                <div class="welcome-text">
                    <span class="welcome-label">Welcome, </span>
                    <span class="user-name">' . htmlspecialchars($displayName) . '</span>
                    <span class="user-role">' . htmlspecialchars($role) . '</span>
                </div>
            </div>
        </div>
    </div>';

if (isset($dbError)) {
    $content .= '
    <div class="error-message" style="background: #ff0040; color: white; padding: 15px; border-radius: 8px; margin: 20px 0;">
        ⚠️ ' . htmlspecialchars($dbError) . '
    </div>';
}

$content .= '
    <!-- Controls Section -->
    <div class="controls-section">
        <div class="controls-left">
            <button class="btn btn-primary add-product-btn" id="addProductBtn">
                <span class="btn-icon">➕</span> Add New Product
            </button>
        </div>
        <div class="controls-right">
            <div class="search-section">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchProducts" placeholder="Search products..." class="search-input">
                </div>
                <select id="categoryFilter" class="category-filter">
                    <option value="">All Categories</option>';

foreach ($categories as $category) {
    $content .= '<option value="' . htmlspecialchars($category) . '">' . htmlspecialchars($category) . '</option>';
}

$content .= '
                </select>
            </div>
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
    $statusText = $product['is_active'] ? 'ACTIVE' : 'INACTIVE';

    $content .= '
                <tr data-product-id="' . htmlspecialchars($product['id']) . '" class="product-row">
                    <td class="product-info">
                        <div class="product-main">' . htmlspecialchars($product['name']) . '</div>
                        <div class="product-description">' . htmlspecialchars(substr($product['description'] ?? '', 0, 80)) . '</div>
                    </td>
                    <td class="product-sku">' . htmlspecialchars($product['sku']) . '</td>
                    <td class="product-category">' . htmlspecialchars($product['category'] ?? '') . '</td>
                    <td class="product-price">$' . number_format($product['price'], 2) . '</td>
                    <td class="product-stock">
                        <div class="stock-display">
                            <span class="stock-amount">Stock: <strong id="stock-' . $product['id'] . '">' . (int)$product['stock_quantity'] . '</strong></span>
                        </div>
                        <div class="stock-controls">
                            <input type="number" class="stock-input" id="add-stock-' . $product['id'] . '" value="0" min="0" placeholder="Add">
                            <button onclick="confirmStockUpdate(\'' . $product['id'] . '\')" class="confirm-btn">Confirm</button>
                        </div>
                    </td>
                    <td class="product-status">
                        <span class="status-badge ' . $statusClass . '">' . $statusText . '</span>
                    </td>
                    <td class="product-actions">
                        <button onclick="editProduct(\'' . $product['id'] . '\')" class="action-btn edit-btn">Edit</button>
                        <button onclick="deleteProduct(\'' . $product['id'] . '\')" class="action-btn delete-btn">Delete</button>
                    </td>
                </tr>';
}

$content .= '
            </tbody>
        </table>
    </div>
</div>

<!-- Product Modal -->
<div id="productModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Product</h2>
            <span class="close" id="closeModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="productForm">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" id="productName" required>
                </div>
                <div class="form-group">
                    <label>SKU *</label>
                    <input type="text" id="productSku" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" id="productCategory">
                </div>
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" id="productPrice" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" id="productStock" value="0">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="productDescription" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Image URL or Local Path</label>
                    <input type="text" id="productImageUrl" placeholder="https://example.com/image.jpg or /assets/img/product.png">
                    <small style="color: #666; font-size: 0.8em;">
                        Enter a web URL (https://...) for new items or leave empty to use CSS-based local images for existing products
                    </small>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="productActive" checked>
                        Active Product
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" id="cancelBtn" class="btn btn-secondary">Cancel</button>
            <button type="button" id="saveProductBtn" class="btn btn-primary">Save Product</button>
        </div>
    </div>
</div>

<div id="notification" class="notification" style="display: none;"></div>

<script>
// Modal functionality
document.getElementById("addProductBtn").addEventListener("click", function() {
    document.getElementById("modalTitle").textContent = "Add Product";
    document.getElementById("productForm").reset();
    document.getElementById("saveProductBtn").dataset.mode = "add";
    document.getElementById("productModal").style.display = "block";
});

document.getElementById("closeModal").addEventListener("click", function() {
    document.getElementById("productModal").style.display = "none";
});

document.getElementById("cancelBtn").addEventListener("click", function() {
    document.getElementById("productModal").style.display = "none";
});

document.getElementById("saveProductBtn").addEventListener("click", function() {
    const mode = this.dataset.mode || "add";
    const productId = this.dataset.productId || "";
    
    saveProduct(mode, productId);
});

// Stock management with confirm - adds amount to existing stock
function confirmStockUpdate(productId) {
    const addStockInput = document.getElementById("add-stock-" + productId);
    const addAmount = parseInt(addStockInput.value);
    
    if (addAmount < 0 || isNaN(addAmount)) {
        showNotification("Please enter a valid amount to add (0 or greater)", "error");
        return;
    }
    
    if (addAmount === 0) {
        showNotification("Please enter an amount greater than 0 to add", "info");
        return;
    }
    
    fetch("/handlers/admin.handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
            action: "update_stock",
            product_id: productId,
            operation: "increase",
            amount: addAmount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById("stock-" + productId).textContent = data.new_stock;
            addStockInput.value = 0; // Reset input to 0 after successful addition
            showNotification(`Added ${addAmount} to stock. New total: ${data.new_stock}`, "success");
        } else {
            showNotification(data.message || "Failed to update stock", "error");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        showNotification("Error updating stock", "error");
    });
}

// Remove the old bulk update function
// function bulkUpdateStock(productId, operation) { ... }

// Product management
function saveProduct(mode, productId = "") {
    const formData = new URLSearchParams({
        action: mode === "add" ? "add_product" : "edit_product",
        name: document.getElementById("productName").value,
        sku: document.getElementById("productSku").value,
        category: document.getElementById("productCategory").value,
        price: document.getElementById("productPrice").value,
        stock_quantity: document.getElementById("productStock").value,
        description: document.getElementById("productDescription").value,
        image_url: document.getElementById("productImageUrl").value,
        is_active: document.getElementById("productActive").checked ? 1 : 0
    });

    if (mode === "edit" && productId) {
        formData.append("product_id", productId);
    }

    fetch("/handlers/admin.handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, "success");
            document.getElementById("productModal").style.display = "none";
            location.reload(); // Refresh to show updated data
        } else {
            showNotification(data.message || "Failed to save product", "error");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        showNotification("Error saving product", "error");
    });
}

function editProduct(productId) {
    fetch("/handlers/admin.handler.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
            action: "get_product",
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const product = data.product;
            document.getElementById("modalTitle").textContent = "Edit Product";
            document.getElementById("productName").value = product.name;
            document.getElementById("productSku").value = product.sku;
            document.getElementById("productCategory").value = product.category || "";
            document.getElementById("productPrice").value = product.price;
            document.getElementById("productStock").value = product.stock_quantity;
            document.getElementById("productDescription").value = product.description || "";
            document.getElementById("productImageUrl").value = product.image_url || "";
            document.getElementById("productActive").checked = product.is_active;
            
            const saveBtn = document.getElementById("saveProductBtn");
            saveBtn.dataset.mode = "edit";
            saveBtn.dataset.productId = productId;
            
            document.getElementById("productModal").style.display = "block";
        } else {
            showNotification(data.message || "Failed to load product", "error");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        showNotification("Error loading product", "error");
    });
}

function deleteProduct(productId) {
    if (confirm("Are you sure you want to delete this product?")) {
        fetch("/handlers/admin.handler.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: "delete_product",
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, "success");
                location.reload(); // Refresh to show updated data
            } else {
                showNotification(data.message || "Failed to delete product", "error");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showNotification("Error deleting product", "error");
        });
    }
}

// Notification system
function showNotification(message, type) {
    const notification = document.getElementById("notification");
    notification.textContent = message;
    notification.className = "notification " + type;
    notification.style.display = "block";
    
    setTimeout(() => {
        notification.style.display = "none";
    }, 3000);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById("productModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
</script>';

// Include the main layout
require_once LAYOUTS_PATH . '/main.layout.php';
?>