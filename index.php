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

// Template variables
$pageTitle = 'Dashboard - AD Final Project';
$pageHeader = 'Welcome to Your Dashboard';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'Dashboard', 'url' => '/dashboard']
];

// Include header template
include BASE_PATH . '/components/templates/header.template.php';
?>

<!-- Dashboard Content -->
<div class="container">
    <div class="dashboard-content">
        <div class="welcome-section">
            <h2>Welcome, <?= htmlspecialchars($user['first_name']) ?>!</h2>
            <p class="user-role">Your role: <span class="role-badge"><?= htmlspecialchars($user['role']) ?></span></p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Projects</h3>
                <p>Manage your projects</p>
                <a href="/projects" class="btn btn-primary">View Projects</a>
            </div>

            <div class="dashboard-card">
                <h3>Tasks</h3>
                <p>Track your tasks</p>
                <a href="/tasks" class="btn btn-primary">View Tasks</a>
            </div>

            <div class="dashboard-card">
                <h3>Profile</h3>
                <p>Update your profile</p>
                <a href="/profile" class="btn btn-primary">Edit Profile</a>
            </div>

            <div class="dashboard-card">
                <h3>Settings</h3>
                <p>Manage your settings</p>
                <a href="/settings" class="btn btn-primary">Settings</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer template
include BASE_PATH . '/components/templates/footer.template.php';
?>