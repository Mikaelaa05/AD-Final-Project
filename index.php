<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';

session_start();

if (!isset($_SESSION['user'])) {
    include ERRORS_PATH . '/unauthorized.error.php';
    exit;
}

$user = $_SESSION['user'];
$pageTitle = 'Dashboard';

// Define the content for the layout
ob_start();
?>
<div class="dashboard-welcome">
    <h1>Welcome, <?= htmlspecialchars($user['first_name']) ?>!</h1>
    <p>Your role: <?= htmlspecialchars($user['role']) ?></p>
    <div class="dashboard-actions">
        <a href="/handlers/logout.handler.php" class="logout">Logout</a>
    </div>
</div>
<?php
$content = ob_get_clean();

// Include the layout
include LAYOUTS_PATH . '/main.layout.php';
?>