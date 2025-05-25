<?php include '../components/header.php'; ?>

<div class="card">
    <h2>Contact Us</h2>
    <p>If you have any questions or comments, feel free to reach out to us!</p>
    <form action="mailto:sky.rocket216@gmail.com" method="post" enctype="text/plain">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name"><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email"><br>
        <label for="message">Message:</label><br>
        <textarea id="message" name="message" rows="5" cols="40" placeholder="Type your message here..."></textarea><br>
        <button type="submit">Submit</button>
</div>

<?php include '../components/back_button.php'; ?>
<?php include '../components/footer.php'; ?>