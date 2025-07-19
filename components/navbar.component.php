<nav>
    <a href="/">Home</a>
    <a href="/pages/Shop">Shop</a>
    <a href="/pages/About">About</a>
    <?php if (isset($_SESSION['user']) && function_exists('isAdmin') && isAdmin()): ?>
        <a href="/pages/Admin">Admin</a>
    <?php endif; ?>
    <a href="/handlers/logout.handler.php">Logout</a>
</nav>