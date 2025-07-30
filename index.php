<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';

session_start();

$pageTitle = 'Home';

// Check if user is logged in for personalized content
$isLoggedIn = isset($_SESSION['user']);
$user = $isLoggedIn ? $_SESSION['user'] : null;

if ($isLoggedIn) {
    // Get user info for welcome message
    $firstName = $user['first_name'] ?? '';
    $lastName = $user['last_name'] ?? '';
    $role = $user['role'] ?? 'User';
    $displayName = trim($firstName . ' ' . $lastName) ?: ($user['username'] ?? 'User');
    $displayRole = $role;
}
// Define the content for the layout
ob_start();
?>
<?php if ($isLoggedIn): ?>
<div class="dashboard-welcome">
    <h1>Welcome, <?= htmlspecialchars($displayName) ?>!</h1>
    <p>Your role: <?= htmlspecialchars($displayRole) ?></p>
</div>
<?php else: ?>
<div class="dashboard-welcome">
    <h1>Welcome to SINTHESIZE Corp.</h1>
    <p>Explore our cybernetic products and digital enhancements</p>
    <p><a href="/pages/Login/index.php" style="color: #00ff7f;">Login</a> or <a href="/pages/SignUp/index.php" style="color: #00ffff;">Register</a> to access our full catalog</p>
</div>
<?php endif; ?>

<!-- Info cards -->
<div class="info-cards">
    <?php if ($isLoggedIn): ?>
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
        <?php if (function_exists('isAdmin') && isAdmin()): ?>
        <div class="info-card">
            <h3>🛠️ Admin</h3>
            <p>Manage products, users, and system settings</p>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="info-card">
            <h3>ℹ️ About</h3>
            <p>Learn more about SINTHESIZE Corp. and our team</p>
        </div>
        <div class="info-card">
            <h3>🔐 Join Us</h3>
            <p>Create an account to access our cybernetic marketplace</p>
        </div>
        <div class="info-card">
            <h3>🏢 Our Vision</h3>
            <p>Leading the future of digital enhancement technology</p>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();

// Include the layout
include LAYOUTS_PATH . '/main.layout.php';
?>