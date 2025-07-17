<?php
/**
 * Authentication Layout
 * Used for login and signup pages
 * Does not include header/navbar since users are not authenticated
 */
?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Authentication'; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>

<body>
    <div class="auth-container">
        <?php echo $content; ?>
    </div>
    <?php include COMPONENTS_PATH . '/footer.component.php'; ?>
</body>

</html>