<?php
require_once __DIR__ . '/../bootstrap.php';
require_once UTILS_PATH . '/envSetter.util.php';

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

try {
    echo "Starting customers table migration...\n";
    
    // Check if username column exists
    $stmt = $pdo->prepare("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name='customers' AND column_name='username'
    ");
    $stmt->execute();
    $usernameExists = $stmt->fetch();
    
    if (!$usernameExists) {
        echo "Adding username column...\n";
        $pdo->exec("ALTER TABLE customers ADD COLUMN username varchar(255) UNIQUE");
        echo "Username column added.\n";
    } else {
        echo "Username column already exists.\n";
    }
    
    // Check if password column exists
    $stmt = $pdo->prepare("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name='customers' AND column_name='password'
    ");
    $stmt->execute();
    $passwordExists = $stmt->fetch();
    
    if (!$passwordExists) {
        echo "Adding password column...\n";
        $pdo->exec("ALTER TABLE customers ADD COLUMN password varchar(255)");
        echo "Password column added.\n";
    } else {
        echo "Password column already exists.\n";
    }
    
    // Add index for username if it doesn't exist
    try {
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_customers_username ON customers(username)");
        echo "Username index created/verified.\n";
    } catch (PDOException $e) {
        echo "Username index already exists or error: " . $e->getMessage() . "\n";
    }
    
    echo "Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
