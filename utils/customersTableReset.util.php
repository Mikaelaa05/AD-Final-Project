<?php
/**
 * Customers Table Reset Utility
 * Universal version - works on any device with proper setup
 * 
 * SETUP REQUIREMENTS:
 * 1. Docker and Docker Compose installed
 * 2. .env file with database configuration
 * 3. PostgreSQL container running
 * 
 * USAGE:
 * docker exec [your-container-name] php utils/customersTableReset.util.php
 */

// Check if bootstrap file exists
$bootstrapPath = __DIR__ . '/../bootstrap.php';
if (!file_exists($bootstrapPath)) {
    echo "❌ Error: bootstrap.php not found!\n";
    echo "   Please ensure you're running this from the project root.\n";
    exit(1);
}

require_once $bootstrapPath;

function resetCustomersTable() {
    try {
        // Validate environment configuration
        $requiredEnvVars = ['POSTGRES_HOST', 'POSTGRES_USER', 'POSTGRES_PASSWORD'];
        $missingVars = [];
        
        foreach ($requiredEnvVars as $var) {
            if (empty($_ENV[$var])) {
                $missingVars[] = $var;
            }
        }
        
        if (!empty($missingVars)) {
            echo "❌ Missing required environment variables:\n";
            foreach ($missingVars as $var) {
                echo "   - $var\n";
            }
            echo "\n📋 Please check your .env file contains:\n";
            echo "   POSTGRES_HOST=postgresql\n";
            echo "   POSTGRES_USER=your_username\n";
            echo "   POSTGRES_PASSWORD=your_password\n";
            echo "   POSTGRES_DB=your_database_name\n";
            exit(1);
        }

        // Get database configuration from environment
        $host = $_ENV['POSTGRES_HOST'];
        $port = $_ENV['POSTGRES_PORT'] ?? '5432';
        $username = $_ENV['POSTGRES_USER'];
        $password = $_ENV['POSTGRES_PASSWORD'];
        $database = $_ENV['POSTGRES_DB'] ?? 'ad_final_project_db';

        echo "🔄 Connecting to PostgreSQL...\n";
        echo "   Host: $host\n";
        echo "   Port: $port\n";
        echo "   Database: $database\n";
        echo "   User: $username\n\n";

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

        // Read customers model SQL
        $modelPath = __DIR__ . '/../database/customers.model.sql';
        if (!file_exists($modelPath)) {
            echo "❌ Error: customers.model.sql not found!\n";
            echo "   Expected location: $modelPath\n";
            echo "   Please ensure the customers model file exists.\n";
            exit(1);
        }

        $sql = file_get_contents($modelPath);
        if (empty($sql)) {
            echo "❌ Error: customers.model.sql is empty!\n";
            echo "   Please check the SQL file contains valid table definition.\n";
            exit(1);
        }

        // Create table
        $pdo->exec($sql);
        echo "   ✅ Created customers table from schema\n";

        // Verify table creation
        $result = $pdo->query("SELECT COUNT(*) as count FROM customers");
        $count = $result->fetch()['count'];

        echo "\n🎉 Customers table reset successful!\n";
        echo "   📊 Current records: $count\n";
        echo "   🗂️  Table structure: customers\n";
        echo "\n💡 Next steps:\n";
        echo "   1. Run seeder: php utils/customersTableSeeder.util.php\n";
        echo "   2. Verify data: php utils/customersTableVerify.util.php\n\n";

        return true;

    } catch (PDOException $e) {
        echo "❌ Database Error: " . $e->getMessage() . "\n\n";
        echo "🔧 Troubleshooting:\n";
        echo "   1. Check if PostgreSQL container is running: docker ps\n";
        echo "   2. Verify .env configuration matches docker-compose.yaml\n";
        echo "   3. Ensure database exists: $database\n";
        echo "   4. Test connection: docker exec [container] psql -U [user] -d [database]\n\n";
        return false;
    } catch (Exception $e) {
        echo "❌ General Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Show usage information
echo "=".str_repeat("=", 60)."=\n";
echo "  CUSTOMERS TABLE RESET UTILITY - UNIVERSAL VERSION\n";
echo "=".str_repeat("=", 60)."=\n\n";

echo "📋 System Requirements Check:\n";
echo "   ✅ PHP: " . PHP_VERSION . "\n";
echo "   ✅ PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? 'Available' : '❌ Missing') . "\n";

if (!extension_loaded('pdo_pgsql')) {
    echo "\n❌ PDO PostgreSQL extension is required!\n";
    echo "   Please install php-pgsql extension.\n";
    exit(1);
}

echo "\n🚀 Starting customers table reset...\n\n";

// Run the reset function
$success = resetCustomersTable();

if ($success) {
    echo "✅ Reset completed successfully!\n";
    exit(0);
} else {
    echo "❌ Reset failed! Please check errors above.\n";
    exit(1);
}
?>
