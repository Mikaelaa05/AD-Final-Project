<?php
$error_message = $_GET['message'] ?? 'Resource not found';
$redirect_url = $_GET['redirect'] ?? '/';
http_response_code(404);
?>
<!DOCTYPE html>
<html>

<head>
    <title>404 Not Found</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/error.css">
</head>

<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <h1>Page Not Found</h1>
        <p><?php echo htmlspecialchars($error_message); ?></p>
        <p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        <a href="<?php echo htmlspecialchars($redirect_url); ?>">Return to Home</a>
    </div>
</body>

</html>
