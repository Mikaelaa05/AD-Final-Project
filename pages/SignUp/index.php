<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';
require_once UTILS_PATH . '/errorHandler.util.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

try {
    $host = $typeConfig['pgHost'];
    $port = $typeConfig['pgPort'];
    $username = $typeConfig['pgUser'];
    $password = $typeConfig['pgPass'];
    $dbname = $typeConfig['pgDb'];
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    error_log("SignUp page database connection failed: " . $e->getMessage());
    ErrorHandler::databaseError('Unable to connect to the registration system', '/pages/SignUp/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($username) || empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "All required fields must be filled.";
    } else {
        try {
            // Check if username already exists in either users or customers table
            if (findUserOrCustomerByUsername($pdo, $username)) {
                $error = "Username already taken.";
            } else {
                // Check if email already exists in customers table
                $emailCheckStmt = $pdo->prepare("SELECT * FROM customers WHERE email = :email LIMIT 1");
                $emailCheckStmt->execute([':email' => $email]);
                if ($emailCheckStmt->fetch(PDO::FETCH_ASSOC)) {
                    $error = "Email already registered.";
                } else {
                    // Insert into customers table
                    $fullName = trim($first_name . ' ' . $last_name);
                    $stmt = $pdo->prepare("
                        INSERT INTO customers (username, name, email, password, phone, address)
                        VALUES (:username, :name, :email, :password, :phone, :address)
                    ");
                    $stmt->execute([
                        ':username' => $username,
                        ':name' => $fullName,
                        ':email' => $email,
                        ':password' => password_hash($password, PASSWORD_DEFAULT),
                        ':phone' => $phone ?: null,
                        ':address' => $address ?: null,
                    ]);
                    $success = "Registration successful! You can now <a href='/pages/Login/index.php'>login</a>.";
                }
            }
        } catch (Exception $e) {
            error_log("SignUp error: " . $e->getMessage());
            ErrorHandler::serverError('Registration failed. Please try again.', '/pages/SignUp/index.php');
        }
    }
}

// Set page title for the layout
$pageTitle = 'Register';

// Define the content for the layout
ob_start();
?>
<div class="brand-container">
    <div class="brand-text">
        <span class="sin">SIN</span><span class="thesize">THESIZE</span>
    </div>
    <div class="tagline">Join the Collective</div>
</div>
<h2>Register</h2>
<form method="POST">
    <input name="username" placeholder="Username" required>
    <input name="first_name" placeholder="First Name" required>
    <input name="last_name" placeholder="Last Name" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="phone" type="tel" placeholder="Phone (optional)">
    <textarea name="address" placeholder="Address (optional)" rows="3"></textarea>
    <input name="password" type="password" placeholder="Password" required>
    <button type="submit">Enter the System</button>
</form>
<?php
if (isset($error))
    echo "<p style='color:red;'>$error</p>";
if (isset($success))
    echo "<p style='color:green;'>$success</p>";
?>
<a href="/pages/Login/index.php">Return to Access Portal</a>
<?php
$content = ob_get_clean();

// Include the auth layout
include LAYOUTS_PATH . '/auth.layout.php';
?>