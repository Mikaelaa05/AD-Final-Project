<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';

session_start();

// Set JSON content type
header('Content-Type: application/json');

// Check if user is authenticated and is an admin
if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

// Database connection
$host = $typeConfig['pgHost'];
$port = $typeConfig['pgPort'];
$username = $typeConfig['pgUser'];
$password = $typeConfig['pgPass'];
$dbname = $typeConfig['pgDb'];
$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_stock':
            updateStock($pdo);
            break;
        case 'update_stock_direct':
            updateStockDirect($pdo);
            break;
        case 'add_product':
            addProduct($pdo);
            break;
        case 'edit_product':
            editProduct($pdo);
            break;
        case 'delete_product':
            deleteProduct($pdo);
            break;
        case 'get_product':
            getProduct($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log("Admin handler error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

function updateStock($pdo) {
    $productId = $_POST['product_id'] ?? '';
    $operation = $_POST['operation'] ?? ''; // 'increase' or 'decrease'
    $amount = (int)($_POST['amount'] ?? 1);

    if (empty($productId) || empty($operation)) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Get current stock
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        $currentStock = (int)$product['stock_quantity'];
        
        if ($operation === 'increase') {
            $newStock = $currentStock + $amount;
        } else if ($operation === 'decrease') {
            $newStock = max(0, $currentStock - $amount); // Don't go below 0
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid operation']);
            return;
        }

        // Update stock
        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$newStock, $productId]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Stock updated successfully',
            'new_stock' => $newStock,
            'product_id' => $productId
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to update stock: ' . $e->getMessage()]);
    }
}

function updateStockDirect($pdo) {
    $productId = $_POST['product_id'] ?? '';
    $newStock = (int)($_POST['new_stock'] ?? 0);

    if (empty($productId) || $newStock < 0) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters or invalid stock value']);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Check if product exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        // Update stock with the new value
        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$newStock, $productId]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Stock updated successfully',
            'new_stock' => $newStock,
            'product_id' => $productId
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to update stock: ' . $e->getMessage()]);
    }
}

function addProduct($pdo) {
    $name = trim($_POST['name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $customCategory = trim($_POST['custom_category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $cost = (float)($_POST['cost'] ?? 0);
    $stockQuantity = (int)($_POST['stock_quantity'] ?? 0);
    $weight = (float)($_POST['weight'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $imageAltText = trim($_POST['image_alt_text'] ?? '');
    $imageCaption = trim($_POST['image_caption'] ?? '');
    $isActive = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true;

    // Use custom category if provided
    if (!empty($customCategory)) {
        $category = $customCategory;
    }

    // Validation
    if (empty($name) || empty($sku) || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Name, SKU, and price are required. Price must be greater than 0.']);
        return;
    }

    try {
        // Check if SKU already exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
        $stmt->execute([$sku]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'SKU already exists. Please use a unique SKU.']);
            return;
        }

        // Validate image URL/path if provided
        if (!empty($imageUrl)) {
            // Check if it's a web URL
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                // Valid web URL - no further validation needed
            } elseif (strpos($imageUrl, '/') === 0) {
                // Local path starting with / - validate it's a reasonable path
                // This is acceptable for local images
            } else {
                echo json_encode(['success' => false, 'message' => 'Image must be a valid URL (https://...) or local path (/assets/img/...)']);
                return;
            }
        }

        // Insert new product
        $stmt = $pdo->prepare("
            INSERT INTO products (
                name, description, category, price, cost, sku, stock_quantity, 
                weight, is_active, image_url, image_alt_text, image_caption
            ) VALUES (
                :name, :description, :category, :price, :cost, :sku, :stock_quantity,
                :weight, :is_active, :image_url, :image_alt_text, :image_caption
            ) RETURNING id
        ");

        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':category' => $category,
            ':price' => $price,
            ':cost' => $cost,
            ':sku' => $sku,
            ':stock_quantity' => $stockQuantity,
            ':weight' => $weight,
            ':is_active' => $isActive,
            ':image_url' => $imageUrl ?: null,
            ':image_alt_text' => $imageAltText ?: null,
            ':image_caption' => $imageCaption ?: null
        ]);

        $productId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

        echo json_encode([
            'success' => true,
            'message' => 'Product added successfully',
            'product_id' => $productId
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to add product: ' . $e->getMessage()]);
    }
}

function getProduct($pdo) {
    $productId = $_POST['product_id'] ?? '';

    if (empty($productId)) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        echo json_encode([
            'success' => true,
            'product' => $product
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to get product: ' . $e->getMessage()]);
    }
}

function editProduct($pdo) {
    $productId = $_POST['product_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $customCategory = trim($_POST['custom_category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $cost = (float)($_POST['cost'] ?? 0);
    $stockQuantity = (int)($_POST['stock_quantity'] ?? 0);
    $weight = (float)($_POST['weight'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $imageAltText = trim($_POST['image_alt_text'] ?? '');
    $imageCaption = trim($_POST['image_caption'] ?? '');
    $isActive = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true;

    // Use custom category if provided
    if (!empty($customCategory)) {
        $category = $customCategory;
    }

    if (empty($productId) || empty($name) || empty($sku) || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Product ID, name, SKU, and price are required. Price must be greater than 0.']);
        return;
    }

    try {
        // Check if SKU exists for another product
        $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
        $stmt->execute([$sku, $productId]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'SKU already exists for another product.']);
            return;
        }

        // Validate image URL/path if provided
        if (!empty($imageUrl)) {
            // Check if it's a web URL
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                // Valid web URL - no further validation needed
            } elseif (strpos($imageUrl, '/') === 0) {
                // Local path starting with / - validate it's a reasonable path
                // This is acceptable for local images
            } else {
                echo json_encode(['success' => false, 'message' => 'Image must be a valid URL (https://...) or local path (/assets/img/...)']);
                return;
            }
        }

        // Update product
        $stmt = $pdo->prepare("
            UPDATE products SET 
                name = :name,
                description = :description,
                category = :category,
                price = :price,
                cost = :cost,
                sku = :sku,
                stock_quantity = :stock_quantity,
                weight = :weight,
                is_active = :is_active,
                image_url = :image_url,
                image_alt_text = :image_alt_text,
                image_caption = :image_caption,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':category' => $category,
            ':price' => $price,
            ':cost' => $cost,
            ':sku' => $sku,
            ':stock_quantity' => $stockQuantity,
            ':weight' => $weight,
            ':is_active' => $isActive,
            ':image_url' => $imageUrl ?: null,
            ':image_alt_text' => $imageAltText ?: null,
            ':image_caption' => $imageCaption ?: null,
            ':id' => $productId
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to update product: ' . $e->getMessage()]);
    }
}

function deleteProduct($pdo) {
    $productId = $_POST['product_id'] ?? '';

    if (empty($productId)) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete product: ' . $e->getMessage()]);
    }
}
?>