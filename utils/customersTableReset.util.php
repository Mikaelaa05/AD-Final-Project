<?php
/**
 * Customers Table Reset Utility
 * Drops and recreates the customers table
 */

require_once __DIR__ . '/../bootstrap.php';

function resetCustomersTable() {
    try {
        // Connect to customers database
        $host = $_ENV['POSTGRES_HOST'] ?? 'localhost';
        $port = $_ENV['POSTGRES_PORT'] ?? '5432';
        $username = $_ENV['POSTGRES_USER'] ?? 'user';
        $password = $_ENV['POSTGRES_PASSWORD'] ?? 'password';
        
        $pdo = new PDO(
            "pgsql:host=$host;port=$port;dbname=customers",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        echo "🔄 Resetting customers table...\n";

        // Drop table if exists
        $pdo->exec("DROP TABLE IF EXISTS customers CASCADE;");
        echo "📤 Existing customers table dropped\n";

        // Recreate table
        $sql = "
            CREATE TABLE customers (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                phone VARCHAR(20),
                address TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ";

        $pdo->exec($sql);
        echo "📥 Customers table recreated successfully\n";
        echo "✅ Reset completed!\n";

    } catch (PDOException $e) {
        echo "❌ Reset failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Run reset
resetCustomersTable();
?>
