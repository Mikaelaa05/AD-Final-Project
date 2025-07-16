<?php
/**
 * Customers Table Reset Utility
 * Using exact credentials from compose.yaml
 */

require_once __DIR__ . '/../bootstrap.php';

function resetCustomersTable() {
    try {
        // Use exact credentials from your compose.yaml
        $host = 'postgresql';  // Container name from compose.yaml
        $port = '5432';        // Internal container port
        $username = 'user';    // POSTGRES_USER from compose.yaml
        $password = 'password'; // POSTGRES_PASSWORD from compose.yaml
        $database = 'ad_final_project_db'; // POSTGRES_DB from compose.yaml

        echo "🔄 Connecting to PostgreSQL...\n";
        echo "   Host: $host\n";
        echo "   Port: $port\n";
        echo "   Database: $database\n";
        echo "   Username: $username\n\n";

        // Create PDO connection
        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        echo "✅ Database connection successful!\n\n";
        echo "🔄 Resetting customers table...\n";

        // Drop table if exists
        $pdo->exec('DROP TABLE IF EXISTS customers CASCADE');
        echo "   ✅ Dropped existing customers table\n";

        // Create customers table schema
        $sql = "
        CREATE TABLE IF NOT EXISTS customers (
            id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
            name varchar(255) NOT NULL,
            email varchar(255) UNIQUE NOT NULL,
            phone varchar(20),
            address text,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE INDEX IF NOT EXISTS idx_customers_email ON customers(email);
        CREATE INDEX IF NOT EXISTS idx_customers_name ON customers(name);
        ";

        $pdo->exec($sql);
        echo "   ✅ Created customers table with schema\n";

        // Verify table creation
        $result = $pdo->query("SELECT COUNT(*) as count FROM customers");
        $count = $result->fetch()['count'];

        echo "\n🎉 Customers table reset successful!\n";
        echo "   📊 Current records: $count\n";
        echo "   🎯 Ready for seeding!\n";

        return true;

    } catch (PDOException $e) {
        echo "❌ Database Error: " . $e->getMessage() . "\n";
        return false;
    } catch (Exception $e) {
        echo "❌ General Error: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "=".str_repeat("=", 50)."=\n";
echo "  CUSTOMERS TABLE RESET UTILITY\n";
echo "=".str_repeat("=", 50)."=\n\n";

$success = resetCustomersTable();

if ($success) {
    echo "✅ Reset completed successfully!\n";
    exit(0);
} else {
    echo "❌ Reset failed!\n";
    exit(1);
}
?>
