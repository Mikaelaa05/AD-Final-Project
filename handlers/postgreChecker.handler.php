<?php

require_once BASE_PATH . '/utils/envSetter.util.php';
require_once BASE_PATH . '/utils/auth.util.php';

echo "<h3>🔍 PostgreSQL & Authentication System Checker</h3>";
echo "<hr>";

// 1. Test PostgreSQL Connection
echo "<h4>1. Testing PostgreSQL Connection:</h4>";
$host = $typeConfig['pgHost'];
$port = $typeConfig['pgPort'];
$username = $typeConfig['pgUser'];
$password = $typeConfig['pgPass'];
$dbname = $typeConfig['pgDb'];

$conn_string = "host=$host port=$port dbname=$dbname user=$username password=$password";
$dbconn = pg_connect($conn_string);

if (!$dbconn) {
    echo "❌ PostgreSQL Connection Failed: " . pg_last_error() . "<br>";
    exit();
} else {
    echo "✅ PostgreSQL Connection Successful<br>";
    pg_close($dbconn);
}

// 2. Test PDO Connection (used by auth handler)
echo "<h4>2. Testing PDO Connection (Auth Handler):</h4>";
try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✅ PDO Connection Successful<br>";
} catch (PDOException $e) {
    echo "❌ PDO Connection Failed: " . $e->getMessage() . "<br>";
    exit();
}

// 3. Test Users Table Existence
echo "<h4>3. Testing Users Table:</h4>";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    echo "✅ Users table exists with {$count} records<br>";
} catch (PDOException $e) {
    echo "❌ Users table test failed: " . $e->getMessage() . "<br>";
}

// 4. Test Auth Utility Functions
echo "<h4>4. Testing Auth Utility Functions:</h4>";

// Test password verification function
$testPassword = "testpass123";
$hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
if (verifyPassword($testPassword, $hashedPassword)) {
    echo "✅ Password verification function works<br>";
} else {
    echo "❌ Password verification function failed<br>";
}

// Test session functions (simulate session)
session_start();
$_SESSION['user'] = ['username' => 'testuser', 'email' => 'test@example.com'];
$_SESSION['user_role'] = 'admin';
$_SESSION['is_admin'] = true;

if (isLoggedIn()) {
    echo "✅ isLoggedIn() function works<br>";
} else {
    echo "❌ isLoggedIn() function failed<br>";
}

if (isAdmin()) {
    echo "✅ isAdmin() function works<br>";
} else {
    echo "❌ isAdmin() function failed<br>";
}

if (getUserRole() === 'admin') {
    echo "✅ getUserRole() function works<br>";
} else {
    echo "❌ getUserRole() function failed<br>";
}

// 5. Test Authentication Handler Logic
echo "<h4>5. Testing Authentication Handler Logic:</h4>";

// Include the determineUserRole function from auth handler
require_once BASE_PATH . '/handlers/auth.handler.php';

// Test admin email pattern
$adminEmail = "2023125674@fit.edu.ph";
$userEmail = "jambolero123@gmail.com";

$adminRole = determineUserRole($adminEmail, $pdo);
$userRole = determineUserRole($userEmail, $pdo);

if ($adminRole === 'admin') {
    echo "✅ Admin email pattern recognition works ('{$adminEmail}' → admin)<br>";
} else {
    echo "❌ Admin email pattern recognition failed ('{$adminEmail}' → {$adminRole})<br>";
}

if ($userRole === 'user') {
    echo "✅ User email pattern recognition works ('{$userEmail}' → user)<br>";
} else {
    echo "❌ User email pattern recognition failed ('{$userEmail}' → {$userRole})<br>";
}

// 6. Test Database Query Functions
echo "<h4>6. Testing Database Query Functions:</h4>";

// Test findUserByUsername with a non-existent user
$nonExistentUser = findUserByUsername($pdo, "nonexistent_user_12345");
if ($nonExistentUser === false) {
    echo "✅ findUserByUsername() handles non-existent users correctly<br>";
} else {
    echo "❌ findUserByUsername() failed for non-existent users<br>";
}

// Clean up test session
session_destroy();

echo "<hr>";
echo "<h4>🎉 PostgreSQL & Authentication System Check Complete!</h4>";
echo "<p><strong>Summary:</strong> All core components have been tested for functionality.</p>";
echo "<p><em>Note: For full authentication testing, ensure you have test users in your database.</em></p>";
?>