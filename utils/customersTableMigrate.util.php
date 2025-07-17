<?php
/**
 * Customers Table Migration Utility
 * Applies the customers.model.sql schema to the database
 */

require_once __DIR__ . '/../bootstrap.php';

function migrateCustomersTable()
{
    try {
        // Use your .env configuration
        $host = $_ENV['PG_HOST'] ?? 'postgresql';
        $port = $_ENV['PG_PORT'] ?? '5432';
        $username = $_ENV['PG_USER'] ?? 'user';
        $password = $_ENV['PG_PASS'] ?? 'password';
        $database = $_ENV['PG_DB'] ?? 'ad_final_project_db';

        echo "🏗️  Migrating customers table...\n";
        echo "   Host: $host:$port\n";
        echo "   Database: $database\n\n";

        // Create PDO connection
        $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        echo "✅ Database connection successful!\n\n";

        // Read and execute the model file
        $modelPath = DATABASE_PATH . '/customers.model.sql';

        if (!file_exists($modelPath)) {
            echo "❌ Model file not found: $modelPath\n";
            return false;
        }

        $sql = file_get_contents($modelPath);

        if (empty($sql)) {
            echo "❌ Model file is empty!\n";
            return false;
        }

        echo "📋 Executing customers model SQL...\n";

        // Execute the model SQL
        $pdo->exec($sql);

        echo "   ✅ Customers table schema applied\n";
        echo "   ✅ Indexes created (email, name)\n";

        // Verify the migration
        echo "\n🔍 Verifying migration...\n";

        // Check table structure
        $columns = $pdo->query("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns 
            WHERE table_name = 'customers' 
            ORDER BY ordinal_position
        ");

        echo "   📋 Table structure:\n";
        foreach ($columns as $column) {
            $nullable = $column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $column['column_default'] ? " (default: {$column['column_default']})" : '';
            echo "      - {$column['column_name']}: {$column['data_type']} {$nullable}{$default}\n";
        }

        // Check indexes
        $indexes = $pdo->query("
            SELECT indexname 
            FROM pg_indexes 
            WHERE tablename = 'customers' 
            AND indexname != 'customers_pkey'
        ");

        echo "   🔗 Indexes:\n";
        foreach ($indexes as $index) {
            echo "      - {$index['indexname']}\n";
        }

        // Check record count
        $countResult = $pdo->query("SELECT COUNT(*) as count FROM customers");
        $count = $countResult->fetch()['count'];

        echo "\n🎉 Customers table migration successful!\n";
        echo "   📊 Current records: $count\n";
        echo "   🎯 Status: Ready for customer data\n";

        return true;

    } catch (PDOException $e) {
        echo "❌ Database Error: " . $e->getMessage() . "\n";
        return false;
    } catch (Exception $e) {
        echo "❌ Migration Error: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "=" . str_repeat("=", 60) . "=\n";
echo "  CUSTOMERS TABLE MIGRATION UTILITY\n";
echo "=" . str_repeat("=", 60) . "=\n\n";

echo "🚀 Starting customers table migration...\n\n";
$success = migrateCustomersTable();

if ($success) {
    echo "\n✅ Migration completed successfully!\n";
    echo "💡 Next step: Seed the table with sample data\n";
    echo "   docker exec adfinalproject-service php utils/customersTableSeeder.util.php\n";
    exit(0);
} else {
    echo "\n❌ Migration failed!\n";
    exit(1);
}
?>