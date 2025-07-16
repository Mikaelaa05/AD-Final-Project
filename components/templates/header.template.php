<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'AD Final Project' ?></title>
    <link rel="stylesheet" href="/assets/css/example.css">
    <?= $additionalCSS ?? '' ?>
</head>

<body>
    <?php include BASE_PATH . '/components/header.component.php'; ?>

    <main class="main-content">
        <?php if (isset($pageHeader)): ?>
            <div class="page-header">
                <div class="container">
                    <h1><?= htmlspecialchars($pageHeader) ?></h1>
                    <?php if (isset($breadcrumbs)): ?>
                        <nav class="breadcrumb">
                            <?php foreach ($breadcrumbs as $crumb): ?>
                                <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['title']) ?></a>
                                <span class="separator">/</span>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>