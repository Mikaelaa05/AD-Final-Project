<nav>
    <a href="/">Home</a>
    <a href="/pages/About">About</a>
    <?php if (isset($_SESSION['user'])): ?>
        <a href="/pages/Shop">Shop</a>
        <a href="/pages/Cart" class="cart-link">
            Cart
            <?php
            $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
            if ($cartCount > 0): 
            ?>
                <span class="cart-count"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>
        <?php if (function_exists('isAdmin') && isAdmin()): ?>
            <a href="/pages/Admin">Admin</a>
        <?php endif; ?>
        <a href="/handlers/logout.handler.php">Logout</a>
    <?php else: ?>
        <a href="/pages/Login/index.php">Login</a>
        <a href="/pages/SignUp/index.php">Register</a>
    <?php endif; ?>
</nav>