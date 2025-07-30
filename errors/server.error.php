<?php
$error_message = $_GET['message'] ?? 'An internal server error occurred';
$redirect_url = $_GET['redirect'] ?? '/';
http_response_code(500);
?>
<!DOCTYPE html>
<html>

<head>
    <title>500 Internal Server Error</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/error.css">
</head>

<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <h1>Internal Server Error</h1>
        <p><?php echo htmlspecialchars($error_message); ?></p>
        <p>Something went wrong on our end. Please try again later.</p>
        <a href="<?php echo htmlspecialchars($redirect_url); ?>">Return to Home</a>
    </div>
</body>

</html>
