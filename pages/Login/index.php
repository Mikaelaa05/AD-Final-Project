<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once HANDLERS_PATH . '/auth.handler.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

// Set page title for the layout
$pageTitle = 'Login';

// Define the content for the layout
ob_start();
?>
<h2>Login</h2>
<form method="POST">
    <input name="username" placeholder="Username" required>
    <input name="password" type="password" placeholder="Password" required>
    <button type="submit">Login</button>
    <?php if (isset($error))
        echo "<p style='color:red;'>$error</p>"; ?>
</form>
<a href="/pages/SignUp/index.php">Don't have an account? Sign up here.</a>
<?php
$content = ob_get_clean();

// Include the auth layout
include LAYOUTS_PATH . '/auth.layout.php';
?>