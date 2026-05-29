<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['roles']      = json_decode($user['roles'], true) ?? ['ROLE_USER'];
}

function logoutUser(): void {
    $_SESSION = [];
    session_destroy();
}


function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function hasRole(string $role): bool {
    if (!isLoggedIn()) return false;
    $roles = $_SESSION['roles'] ?? [];
    return in_array($role, $roles, true);
}

function isAdmin(): bool {
    return hasRole('ROLE_ADMIN');
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('danger', 'You must be logged in to access that page.');
        header('Location: /login');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        setFlash('danger', 'Access denied.');
        header('Location: /');
        exit;
    }
}


function setFlash(string $type, string $message): void {
    $_SESSION['flashes'][$type][] = $message;
}

function getFlashes(): array {
    $flashes = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);
    return $flashes;
}
