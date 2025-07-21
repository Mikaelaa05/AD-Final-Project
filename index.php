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

// Redirect customers to shop, admins to admin panel
if ($accountType === 'customer') {
    header('Location: /pages/Shop');
    exit;
} else if (isAdmin()) {
    header('Location: /pages/Admin');
    exit;
} else {
    // Regular users go to shop as well
    header('Location: /pages/Shop');
    exit;
}

// Define the content for the layout
ob_start();
?>
<div class="dashboard-welcome">
    <h1>Welcome, <?= htmlspecialchars($displayName) ?>!</h1>
    <p>Your role: <?= htmlspecialchars($displayRole) ?></p>
    <div class="dashboard-actions">
        <a href="/handlers/logout.handler.php" class="logout">Logout</a>
    </div>
</div>
<?php
$content = ob_get_clean();

// Include the layout
include LAYOUTS_PATH . '/main.layout.php';
?>