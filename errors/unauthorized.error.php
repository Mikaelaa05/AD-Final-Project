<?php
http_response_code(401);
?>
<!DOCTYPE html>
<html>

<head>
    <title>401 Unauthorized</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/error.css">
</head>

<body>
    <div class="error-container">
        <div class="error-code">401</div>
        <h1>Unauthorized</h1>
        <p>You must be logged in to view this page.</p>
        <a href="/pages/Login/index.php">Go to Login</a>
    </div>
</body>

</html>