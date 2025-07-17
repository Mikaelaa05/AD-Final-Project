<?php

function isAuthenticated(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

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


