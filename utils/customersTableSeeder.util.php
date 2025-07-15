<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'bootstrap.php';
require_once UTILS_PATH . '/envSetter.util.php';

// Helper to generate UUID v4
function generate_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

$host = $typeConfig['pgHost'];
$port = $typeConfig['pgPort'];
$username = $typeConfig['pgUser'];
$password = $typeConfig['pgPass'];
$dbname = $typeConfig['pgDb'];

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "🌱 Seeding customers table…\n";

// Create table if not exists
$sql = file_get_contents('database/customers.model.sql');
if ($sql === false) {
    throw new RuntimeException("❌ Could not read customers.model.sql");
}
$pdo->exec($sql);

// Clear existing data
try {
    $pdo->exec("TRUNCATE TABLE customers RESTART IDENTITY CASCADE;");
} catch (PDOException $e) {
    echo "Warning: Could not truncate customers table: " . $e->getMessage() . "\n";
}

// Seed customers data
$customers = require DUMMIES_PATH . '/customers.staticData.php';
$stmt = $pdo->prepare("
    INSERT INTO customers (
        id, customer_code, company_name, first_name, last_name, email, phone,
        address_line1, address_line2, city, state, postal_code, country,
        customer_type, credit_limit, total_orders, total_spent, is_active, notes
    ) VALUES (
        :id, :customer_code, :company_name, :first_name, :last_name, :email, :phone,
        :address_line1, :address_line2, :city, :state, :postal_code, :country,
        :customer_type, :credit_limit, :total_orders, :total_spent, :is_active, :notes
    )
");

$customerCount = 0;
foreach ($customers as $c) {
    $uuid = generate_uuid();
    $stmt->execute([
        ':id' => $uuid,
        ':customer_code' => $c['customer_code'],
        ':company_name' => $c['company_name'],
        ':first_name' => $c['first_name'],
        ':last_name' => $c['last_name'],
        ':email' => $c['email'],
        ':phone' => $c['phone'],
        ':address_line1' => $c['address_line1'],
        ':address_line2' => $c['address_line2'],
        ':city' => $c['city'],
        ':state' => $c['state'],
        ':postal_code' => $c['postal_code'],
        ':country' => $c['country'],
        ':customer_type' => $c['customer_type'],
        ':credit_limit' => $c['credit_limit'],
        ':total_orders' => $c['total_orders'],
        ':total_spent' => $c['total_spent'],
        ':is_active' => $c['is_active'] ? 'true' : 'false',
        ':notes' => $c['notes'],
    ]);
    $customerCount++;
}

echo "✅ Successfully seeded {$customerCount} customers!\n";

// Display seeded data summary
echo "\n📊 Customer Database Summary:\n";
$result = $pdo->query("
    SELECT 
        customer_type,
        COUNT(*) as count,
        ROUND(AVG(credit_limit), 2) as avg_credit_limit,
        ROUND(SUM(total_spent), 2) as total_revenue
    FROM customers 
    GROUP BY customer_type
");

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$row['customer_type']}: {$row['count']} customers, Avg Credit: ${$row['avg_credit_limit']}, Total Revenue: ${$row['total_revenue']}\n";
}
