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

    <!-- Page Content -->
    <?= $content ?>
</main>

<?php include BASE_PATH . '/components/templates/footer.component.php'; ?>
<script src="/assets/js/example.js"></script>
<?= $additionalJS ?? '' ?>