<?php include 'components/header.php'; ?>

<?php $isBind = true;

function displayBandMembers($members)
{
    echo "<p>Kessoku Band Members:</p><ul>";
    foreach ($members as $member) {
        echo "<li>" . $member . "</li>";
    }
    echo "</ul>";
}

?>

<div class="card">
    <h2>Welcome to the <?php echo $theme; ?> Fan Page!</h2>
    <p>This is a fan page dedicated to the amazing anime series "<?php echo $theme; ?>" and its characters. To do this,
        it utilizes the basics of PHP!</p>
    <p>For more information, visit the Bocchi the Rock! <a href="https://bocchi.rocks">official website</a> here!</p>
</div>

<div class="card">
    <h3>PHP Basics</h3>
    <?php
    $members = [
        'Gotoh Hitori',
        'Kita Ikuyo',
        'Ichiji Nijika',
        'Yamada Ryo'
    ];
    if ($isBind) {
        displayBandMembers($members); // Call the function to display members
    } else {
        echo "<p>Not a member of Kessoku Band</p>";
    }
    ?>
</div>

<?php include 'components/footer.php'; ?>