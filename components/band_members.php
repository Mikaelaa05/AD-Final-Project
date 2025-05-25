<?php
function displayBandMembers($members)
{
    echo "<p>Kessoku Band Members:</p><ul>";
    foreach ($members as $member) {
        echo "<li>" . $member . "</li>";
    }
    echo "</ul>";
}
?>