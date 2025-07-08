<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once BASE_PATH . '/handlers/auth.handler.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

// If POST and login failed, $error may be set by auth.handler.php
?>
<!DOCTYPE html>
<html>

    <head>
        <title>Login</title>
    </head>

    <body>
        <h2>Login</h2>
        <form method="POST">
            <input name="username" placeholder="Username" required>
            <input name="password" type="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <?php if (isset($error))
                echo "<p style='color:red;'>$error</p>"; ?>
        </form>
        <a href="/pages/SignUp/index.php">Don't have an account? Sign up here.</a>
    </body>

</html>