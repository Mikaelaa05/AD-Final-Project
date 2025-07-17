<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/bootstrap.php';
require_once BASE_PATH . '/handlers/mongodbChecker.handler.php';
require_once BASE_PATH . '/handlers/postgreChecker.handler.php';

session_start();

if (!isset($_SESSION['user'])) {
    include BASE_PATH . '/errors/unauthorized.error.php';
    exit;
}

$user = $_SESSION['user'];

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
?>
<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>

<body>
    <?php include BASE_PATH . '/layouts/main.layout.php'; ?>
</body>

</html>