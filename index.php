<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: /pages/Login/index.php');
    exit;
}

$user = $_SESSION['user'];
$accountType = $user['account_type'] ?? 'user';
$pageTitle = 'Dashboard';

// Get user info for welcome message
$firstName = $user['first_name'] ?? '';
$lastName = $user['last_name'] ?? '';
$role = $user['role'] ?? 'User';
$displayName = trim($firstName . ' ' . $lastName) ?: ($user['username'] ?? 'User');
$displayRole = $role;

// Define the content for the layout
ob_start();
?>
<div class="dashboard-welcome">
    <h1>Welcome, <?= htmlspecialchars($displayName) ?>!</h1>
    <p>Your role: <?= htmlspecialchars($displayRole) ?></p>
</div>

<!-- Info cards -->
<div class="info-cards">
    <div class="info-card">
        <h3>🛒 Shop</h3>
        <p>Browse our cybernetic products and digital enhancements</p>
    </div>
    <div class="info-card">
        <h3>🛍️ Cart</h3>
        <p>Manage your selected items and proceed to checkout</p>
    </div>
    <div class="info-card">
        <h3>ℹ️ About</h3>
        <p>Learn more about SINTHESIZE Corp. and our team</p>
    </div>
    <?php if (isAdmin()): ?>
    <div class="info-card">
        <h3>🛠️ Admin</h3>
        <p>Manage products, users, and system settings</p>
    </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();

// Include the layout
include LAYOUTS_PATH . '/main.layout.php';
?>