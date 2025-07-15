<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'bootstrap.php';
require_once UTILS_PATH . '/envSetter.util.php';

$host = $typeConfig['pgHost'];
$port = $typeConfig['pgPort'];
$username = $typeConfig['pgUser'];
$password = $typeConfig['pgPass'];
$dbname = $typeConfig['pgDb'];

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "🔍 Verifying customers database…\n";

try {
    // Check if table exists
    $result = $pdo->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'customers'
        )
    ");
    $tableExists = $result->fetchColumn();
    
    if (!$tableExists) {
        echo "❌ Customers table does not exist!\n";
        exit(1);
    }
    
    echo "✅ Customers table exists\n";
    
    // Get total customer count
    $result = $pdo->query("SELECT COUNT(*) FROM customers");
    $totalCustomers = $result->fetchColumn();
    echo "📊 Total customers: {$totalCustomers}\n";
    
    if ($totalCustomers > 0) {
        // Customer type breakdown
        echo "\n📈 Customer Type Breakdown:\n";
        $result = $pdo->query("
            SELECT 
                customer_type,
                COUNT(*) as count,
                ROUND(AVG(credit_limit), 2) as avg_credit_limit,
                ROUND(SUM(total_spent), 2) as total_revenue
            FROM customers 
            GROUP BY customer_type
            ORDER BY count DESC
        ");
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['customer_type']}: {$row['count']} customers\n";
            echo "    Avg Credit Limit: ${$row['avg_credit_limit']}\n";
            echo "    Total Revenue: ${$row['total_revenue']}\n\n";
        }
        
        // Active vs Inactive
        echo "🟢 Active/Inactive Status:\n";
        $result = $pdo->query("
            SELECT 
                is_active,
                COUNT(*) as count
            FROM customers 
            GROUP BY is_active
        ");
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['is_active'] ? 'Active' : 'Inactive';
            echo "  - {$status}: {$row['count']} customers\n";
        }
        
        // Top customers by spending
        echo "\n💰 Top 3 Customers by Total Spent:\n";
        $result = $pdo->query("
            SELECT 
                customer_code,
                CASE 
                    WHEN company_name IS NOT NULL THEN company_name
                    ELSE first_name || ' ' || last_name
                END as name,
                total_spent,
                total_orders
            FROM customers 
            ORDER BY total_spent DESC 
            LIMIT 3
        ");
        
        $rank = 1;
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$rank}. {$row['name']} ({$row['customer_code']})\n";
            echo "     Total Spent: ${$row['total_spent']}\n";
            echo "     Orders: {$row['total_orders']}\n\n";
            $rank++;
        }
        
        // Sample customer data
        echo "📝 Sample Customer Records:\n";
        $result = $pdo->query("
            SELECT 
                customer_code,
                first_name,
                last_name,
                email,
                customer_type,
                city,
                state
            FROM customers 
            LIMIT 3
        ");
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "  • {$row['customer_code']}: {$row['first_name']} {$row['last_name']} ({$row['customer_type']})\n";
            echo "    📧 {$row['email']}\n";
            echo "    📍 {$row['city']}, {$row['state']}\n\n";
        }
    } else {
        echo "⚠️  No customers found in database\n";
    }
    
    echo "✅ Customer database verification complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
