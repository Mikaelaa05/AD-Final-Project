<?php
/**
 * Test Page Router
 * Simple standalone file to access the test page directly
 * Access via: http://localhost:9000/test.php
 */

require_once __DIR__ . '/bootstrap.php';

// Direct access to test page - no authentication required
include BASE_PATH . '/pages/TestPage/index.php';
?>