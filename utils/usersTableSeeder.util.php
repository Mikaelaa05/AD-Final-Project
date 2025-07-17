<?php
declare(strict_types=1);
/**
 * Users Table Seeder Utility
 * Seeds the users table with team member data
 */

require_once __DIR__ . '/../bootstrap.php';

function seedUsersTable() {
    try {
        // Use your .env configuration
        $host = $_ENV['PG_HOST'] ?? 'postgresql';
        $port = $_ENV['PG_PORT'] ?? '5432';
        $username = $_ENV['PG_USER'] ?? 'user';
        $password = $_ENV['PG_PASS'] ?? 'password';
        $database = $_ENV['PG_DB'] ?? 'ad_final_project_db';
        
        $pdo = new PDO(
            "pgsql:host=$host;port=$port;dbname=$database",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        echo "🌱 Seeding users table with team data...\n\n";

        // Clear existing data
        $pdo->exec("TRUNCATE TABLE users RESTART IDENTITY CASCADE;");
        echo "   ✅ Cleared existing user data\n";

        // Load user data from static file
        $usersFile = __DIR__ . '/../staticData/dummies/users.staticData.php';
        
        if (!file_exists($usersFile)) {
            echo "❌ Users static data file not found!\n";
            echo "   Expected: $usersFile\n";
            return false;
        }

        $users = require $usersFile;

        if (empty($users)) {
            echo "❌ No user data found in static file!\n";
            // Use hardcoded data as fallback
            $users = [
                [
                    'username' => 'H1H3',
                    'first_name' => 'Boris',
                    'last_name' => 'Dela Cruz',
                    'password' => 'admin',
                    'email' => '202311499@fit.edu.ph',
                    'phone' => '09123456789',
                    'role' => 'Database Manager'
                ],
                [
                    'username' => 'MikaTheRock',
                    'first_name' => 'Mikaela Andrea',
                    'last_name' => 'Cid',
                    'password' => 'admin',
                    'email' => '202310289@fit.edu.ph',
                    'phone' => '09876543210',
                    'role' => 'Quality Assurance Manager'
                ],
                [
                    'username' => 'SusPeekZ',
                    'first_name' => 'Jan-Michael II',
                    'last_name' => 'Laguesma',
                    'password' => 'admin',
                    'email' => '202312061@fit.edu.ph',
                    'phone' => '09234567891',
                    'role' => 'Backend'
                ],
                [
                    'username' => 'Jam',
                    'first_name' => 'Baron Jamille',
                    'last_name' => 'Andres',
                    'password' => 'admin',
                    'email' => '202311934@fit.edu.ph',
                    'phone' => '09345678912',
                    'role' => 'Designer'
                ],
                [
                    'username' => 'Waffle',
                    'first_name' => 'Syrrlian',
                    'last_name' => 'Castro',
                    'password' => 'admin',
                    'email' => '202312208@fit.edu.ph',
                    'phone' => '09456789123',
                    'role' => 'Front-End Developer'
                ]
            ];
            echo "   📋 Using hardcoded FIT email data\n";
        }

        // Insert users
        $stmt = $pdo->prepare("
            INSERT INTO users (username, first_name, last_name, password, email, phone, role) 
            VALUES (:username, :first_name, :last_name, :password, :email, :phone, :role)
        ");

        $insertedCount = 0;
        foreach ($users as $user) {
            // Hash the password
            $hashedPassword = password_hash($user['password'], PASSWORD_BCRYPT);
            
            $stmt->execute([
                'username' => $user['username'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'password' => $hashedPassword,
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role']
            ]);
            
            $insertedCount++;
            echo "👤 ✅ {$user['first_name']} {$user['last_name']} ({$user['username']}) - {$user['email']}\n";
        }

        echo "\n🎉 Successfully seeded $insertedCount team members!\n";
        echo "💡 All team members can login with password: 'admin'\n";

        return true;

    } catch (PDOException $e) {
        echo "❌ Database error: " . $e->getMessage() . "\n";
        return false;
    } catch (Exception $e) {
        echo "❌ Seeding failed: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "=" . str_repeat("=", 60) . "=\n";
echo "  USERS TABLE SEEDER UTILITY\n";
echo "=" . str_repeat("=", 60) . "=\n\n";

$success = seedUsersTable();

if ($success) {
    echo "✅ Seeding completed successfully!\n";
    exit(0);
} else {
    echo "❌ Seeding failed!\n";
    exit(1);
}
