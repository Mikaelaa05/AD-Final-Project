<?php
$error_message = $_GET['message'] ?? 'Invalid request';
$redirect_url = $_GET['redirect'] ?? '/';
http_response_code(400);
?>
<!DOCTYPE html>
<html>

<head>
    <title>400 Bad Request</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/error.css">
</head>

<body>
    <div class="error-container">
        <div class="error-code">400</div>
        <h1>Bad Request</h1>
        <p><?php echo htmlspecialchars($error_message); ?></p>
        <p>Please check your request and try again.</p>
        <a href="<?php echo htmlspecialchars($redirect_url); ?>">Return to Home</a>
    </div>
</body>

</html>
