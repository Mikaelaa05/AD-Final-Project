<?php
/**
 * Customers Table Seeder Utility
 * Seeds the customers table with website signup data
 */

require_once __DIR__ . '/../bootstrap.php';

function seedCustomersTable()
{
    try {
        // Connect to main database (customers table is in the same DB)
        $host = $_ENV['PG_HOST'] ?? $_ENV['POSTGRES_HOST'] ?? 'postgresql';
        $port = $_ENV['PG_PORT'] ?? $_ENV['POSTGRES_PORT'] ?? '5432';
        $username = $_ENV['PG_USER'] ?? $_ENV['POSTGRES_USER'] ?? 'user';
        $password = $_ENV['PG_PASS'] ?? $_ENV['POSTGRES_PASSWORD'] ?? 'password';

        $pdo = new PDO(
            "pgsql:host=$host;port=$port;dbname=ad_final_project_db",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        echo "🌱 Seeding customers table (website signups)...\n";

        // Check if table has data
        $countResult = $pdo->query("SELECT COUNT(*) FROM customers");
        $existingCount = $countResult->fetchColumn();

        if ($existingCount > 0) {
            echo "⚠️  Table already contains $existingCount records. Clearing first...\n";
            $pdo->exec("TRUNCATE TABLE customers RESTART IDENTITY CASCADE;");
        }

        // Load customer data from static file
        $customers = require DUMMIES_PATH . '/customers.staticData.php';

        // Insert customers
        $stmt = $pdo->prepare("
            INSERT INTO customers (name, email, phone, address) 
            VALUES (:name, :email, :phone, :address)
        ");

        $insertedCount = 0;
        foreach ($customers as $customer) {
            $stmt->execute($customer);
            $insertedCount++;
            echo "👤 Added customer: {$customer['name']}\n";
        }

        echo "\n✅ Successfully seeded $insertedCount customers from website signups!\n";

        // Show summary
        $result = $pdo->query("SELECT COUNT(*) as total FROM customers");
        $total = $result->fetch(PDO::FETCH_ASSOC)['total'];
        echo "📊 Total customers in database: $total\n";
        echo "💡 Note: These are website signups, not admin users!\n";

    } catch (PDOException $e) {
        echo "❌ Seeding failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Run seeder
seedCustomersTable();
?>