<?php
/**
 * Customers Table Migration Utility
 * Creates the customers table in the customers database
 */

require_once '../bootstrap.php';

function migrateCustomersTable() {
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

        // Create customers table
        $sql = "
            CREATE TABLE IF NOT EXISTS customers (
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
        echo "✅ Customers table migration completed successfully!\n";
        
        // Check if table exists and show structure
        $result = $pdo->query("
            SELECT column_name, data_type, is_nullable, column_default 
            FROM information_schema.columns 
            WHERE table_name = 'customers' 
            ORDER BY ordinal_position
        ");
        
        echo "\n📋 Table Structure:\n";
        echo "Column Name    | Data Type      | Nullable | Default\n";
        echo "---------------|----------------|----------|--------\n";
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            printf("%-14s | %-14s | %-8s | %s\n", 
                $row['column_name'], 
                $row['data_type'], 
                $row['is_nullable'], 
                $row['column_default'] ?? 'NULL'
            );
        }

    } catch (PDOException $e) {
        echo "❌ Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Run migration
migrateCustomersTable();
?>
