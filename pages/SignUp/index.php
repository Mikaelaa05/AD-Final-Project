<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once BASE_PATH . '/utils/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$host = $typeConfig['pgHost'];
$port = $typeConfig['pgPort'];
$username = $typeConfig['pgUser'];
$password = $typeConfig['pgPass'];
$dbname = $typeConfig['pgDb'];
$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    // Check if username already exists
    if (findUserByUsername($pdo, $username)) {
        $error = "Username already taken.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, first_name, last_name, password, role)
            VALUES (:username, :first_name, :last_name, :password, :role)
        ");
        $stmt->execute([
            ':username' => $username,
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => $role,
        ]);
        $success = "Registration successful! You can now <a href='/pages/Login/index.php'>login</a>.";
    }
}

// Set page title for the layout
$pageTitle = 'Register';

// Define the content for the layout
ob_start();
?>
<h2>Sign Up</h2>
<form method="POST">
    <input name="username" placeholder="Username" required>
    <input name="first_name" placeholder="First Name" required>
    <input name="last_name" placeholder="Last Name" required>
    <input name="password" type="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form>
<?php
if (isset($error))
    echo "<p style='color:red;'>$error</p>";
if (isset($success))
    echo "<p style='color:green;'>$success</p>";
?>
<a href="/pages/Login/index.php">Back to Login</a>
<?php
$content = ob_get_clean();
//TODO Backen, fix Signup please
// Include the auth layout
include BASE_PATH . '/layouts/auth.layout.php';
?>