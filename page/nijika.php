<?php include '../components/header.php'; ?>

<div class="card nijika-card">
    <h2>Ichiji Nijika</h2>
    <div class="character-image-container">
        <button class="image-nav left" onclick="changeImage('left')">←</button>
        <img src="<?php echo BASE_URL; ?>assets/img/ichiji_nijika_1.jpg" alt="Ichiji Nijika" id="character-image">
        <button class="image-nav right" onclick="changeImage('right')">→</button>
    </div>
    <p>Ichiji Nijika is the drummer of the Kessoku Band. She is known for her energetic personality and love for music.
    </p>
</div>

<div class="navigation-buttons">
    <a href="kita.php" class="nav-button">← Previous</a>
    <a href="ryo.php" class="nav-button">Next →</a>
</div>

<?php include '../components/back_button.php'; ?>
<?php include '../components/footer.php'; ?>