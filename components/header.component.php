<header class="main-header"></header>
</header>
<div class="container">
    <div class="header-content">
        <div class="logo">
            <a href="/">
                <img src="/assets/img/nyebe_white.png" alt="Logo" class="logo-img">
                <span class="logo-text">AD Final Project</span>
            </a>
        </div>
        <nav class="main-nav">
            <?php include BASE_PATH . '/components/navbar.component.php'; ?>
        </nav>
        <div class="header-actions">
            <?php if (isset($_SESSION['user'])): ?>
                <span class="user-greeting">Hello, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'User') ?></span>
                <a href="/handlers/logout.handler.php" class="btn btn-outline">Logout</a>
            <?php else: ?>
                <a href="/pages/Login/" class="btn btn-primary">Login</a>
                <a href="/pages/SignUp/" class="btn btn-outline">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</header>