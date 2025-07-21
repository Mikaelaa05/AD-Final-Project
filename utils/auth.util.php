<?php

function isAuthenticated(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

function isAdmin(): bool
{
    if (!isAuthenticated()) {
        return false;
    }
    
    // Simple admin check - if user has role field and it contains admin-like role
    $user = $_SESSION['user'] ?? [];
    $role = $user['role'] ?? '';
    
    $adminRoles = ['admin', 'administrator', 'Database Manager', 'Quality Assurance Manager', 'Backend', 'Designer', 'Front-End Developer'];
    
    return in_array($role, $adminRoles);
}
function findUserByUsername(PDO $pdo, string $username)
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function findCustomerByUsername(PDO $pdo, string $username)
{
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function findUserOrCustomerByUsername(PDO $pdo, string $username)
{
    // First try to find in users table (admins/staff)
    $user = findUserByUsername($pdo, $username);
    if ($user) {
        $user['account_type'] = 'user';
        return $user;
    }
    
    // If not found, try customers table
    $customer = findCustomerByUsername($pdo, $username);
    if ($customer) {
        $customer['account_type'] = 'customer';
        // Add missing fields for compatibility
        $customer['role'] = 'customer';
        $nameParts = explode(' ', $customer['name'], 2);
        $customer['first_name'] = $nameParts[0] ?? '';
        $customer['last_name'] = $nameParts[1] ?? '';
        return $customer;
    }
    
    return false;
}

function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}