<?php
require_once UTILS_PATH . '/auth.util.php';
require_once UTILS_PATH . '/envSetter.util.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in and redirect accordingly
if (isLoggedIn()) {
    // User is already logged in, redirect based on their role
    if (isAdmin()) {
        header('Location: /admin'); // Redirect admins to admin dashboard
    } else {
        header('Location: /'); // Redirect users to regular dashboard
    }
    exit;
}

// If not a POST request (login attempt), redirect to login page
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Login'); // Redirect to login page
    exit;
}

function determineUserRole($email, $pdo = null) {
    // TODO: Edit this section to match your specific admin email patterns
    

    $meetsAdminPattern = str_starts_with($email, '2023') && str_ends_with($email, '@fit.edu.ph');
    
    if ($meetsAdminPattern && $pdo !== null) {
        // Count current admins in the database
        $stmt = $pdo->prepare("SELECT COUNT(*) as admin_count FROM users WHERE email LIKE '2023%@fit.edu.ph'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentAdminCount = $result['admin_count'];
        
        // Check if this user's email is already in the admin count
        $stmt = $pdo->prepare("SELECT COUNT(*) as user_exists FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $userExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userExists['user_exists'] > 0) {
            // This user already exists and has admin pattern email, they remain admin
            return 'admin';
        } else if ($currentAdminCount < 5) {
            // New user with admin pattern and we haven't reached the limit
            return 'admin';
        } else {
            // Admin limit reached, assign as user
            return 'user';
        }
    }
    
    // Default role is user
    return 'user';
}

// Connect to DB
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
    $password = $_POST['password'] ?? '';

    $user = findUserByUsername($pdo, $username);
    if ($user && verifyPassword($password, $user['password'])) {
        // Determine user role based on email (with admin limit check)
        $userRole = determineUserRole($user['email'], $pdo);
        
        // Store user data and role in session
        $_SESSION['user'] = $user;
        $_SESSION['user_role'] = $userRole;
        $_SESSION['is_admin'] = ($userRole === 'admin');
        
        // Redirect based on role (you can customize these redirects)
        if ($userRole === 'admin') {
            header('Location: /admin'); // Redirect admins to admin dashboard
        } else {
            header('Location: /'); // Redirect users to regular dashboard
        }
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}