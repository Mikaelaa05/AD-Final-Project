<?php
$error_code = $_GET['code'] ?? 500;
$error_message = $_GET['message'] ?? 'Database connection failed';
$redirect_url = $_GET['redirect'] ?? '/';
http_response_code($error_code);
?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo $error_code; ?> Database Error</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/error.css">
</head>

<body>
    <div class="error-container">
        <div class="error-code"><?php echo htmlspecialchars($error_code); ?></div>
        <h1>Database Error</h1>
        <p><?php echo htmlspecialchars($error_message); ?></p>
        <p>Please try again later or contact the administrator if the problem persists.</p>
        <a href="<?php echo htmlspecialchars($redirect_url); ?>">Return to Home</a>
    </div>
</body>

</html>
