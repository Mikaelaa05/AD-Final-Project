<?php
/**
 * Frontend Test Page
 * This page bypasses authentication and database requirements
 * Use this for testing frontend components and functionality
 */

// Include bootstrap and required utilities
require_once __DIR__ . '/../../bootstrap.php';
require_once BASE_PATH . '/utils/htmlEscape.utils.php';

// Mock user data for testing (no database required)
$mockUser = [
    'id' => 1,
    'first_name' => 'Test',
    'last_name' => 'User',
    'email' => 'test@example.com',
    'role' => 'developer'
];

// Set up mock session for testing
if (!session_id()) {
    session_start();
}
$_SESSION['user'] = $mockUser;

// Set the content file for the main layout
$content = __DIR__ . '/content.php';

// Include the main layout
include BASE_PATH . '/layouts/main.layout.php';
?>