<?php

function findUserByUsername(PDO $pdo, string $username)
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Check if current user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

/**
 * Check if current user is an admin
 */
function isAdmin(): bool {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Get current user's role
 */
function getUserRole(): string {
    return $_SESSION['user_role'] ?? 'guest';
}

/**
 * Require user to be logged in, redirect to login if not
 */
function requireLogin($redirectTo = '/login') {
    if (!isLoggedIn()) {
        header("Location: $redirectTo");
        exit;
    }
}

/**
 * Require user to be admin, redirect or show error if not
 */
function requireAdmin($redirectTo = '/unauthorized') {
    requireLogin();
    if (!isAdmin()) {
        header("Location: $redirectTo");
        exit;
    }
}


