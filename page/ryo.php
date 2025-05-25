<?php include '../components/header.php'; ?>

<div class="card ryo-card">
    <h2>Yamada Ryo</h2>
    <div class="character-image-container">
        <button class="image-nav left" onclick="changeImage('left')">←</button>
        <img src="<?php echo BASE_URL; ?>assets/img/yamada_ryo_1.jpg" alt="Yamada Ryo" id="character-image">
        <button class="image-nav right" onclick="changeImage('right')">→</button>
    </div>
    <p>Yamada Ryo is the band's bassist. She is cool and mysterious with a quirky attitude.</p>
</div>

<div class="navigation-buttons">
    <a href="nijika.php" class="nav-button">← Previous</a>
    <a href="hitori.php" class="nav-button">Next →</a>
</div>

<?php include '../components/back_button.php'; ?>
<?php include '../components/footer.php'; ?>