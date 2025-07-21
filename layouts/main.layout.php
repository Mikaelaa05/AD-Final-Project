<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Company Dashboard</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/navbar.css">
    <?php if (isset($pageCSS))
        echo $pageCSS; ?>
</head>

<body>
    <?php include COMPONENTS_PATH . '/header.component.php'; ?>
    <main>
        <?php echo $content; ?>
    </main>
    <?php include COMPONENTS_PATH . '/footer.component.php'; ?>
</body>

</html>