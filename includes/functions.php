<?php

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Customer.php';
require_once __DIR__ . '/../classes/Admin.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function sanitizeInternalRedirect(string $path): string
{
    $path = trim($path);

    if ($path === '') {
        return '';
    }

    $baseUrl = $GLOBALS['base_url'] ?? '';

    if ($baseUrl === '' || strpos($path, $baseUrl . '/') !== 0) {
        return '';
    }

    return $path;
}

function setFlash(string $key, string $message): void
{
    $_SESSION[$key] = $message;
}

function getFlash(string $key): ?string
{
    if (!isset($_SESSION[$key])) {
        return null;
    }

    $message = $_SESSION[$key];
    unset($_SESSION[$key]);

    return $message;
}

function buildUserObject(array $userData): User
{
    if (($userData['role'] ?? 'customer') === 'admin') {
        return new Admin(
            (int)$userData['id'],
            $userData['name'],
            $userData['email'],
            $userData['username'],
            $userData['role']
        );
    }

    return new Customer(
        (int)$userData['id'],
        $userData['name'],
        $userData['email'],
        $userData['username'],
        $userData['role'] ?? 'customer'
    );
}

function currentUser(): ?User
{
    $requiredKeys = [
        'user_id',
        'user_name',
        'user_email',
        'user_username',
        'user_role',
    ];

    foreach ($requiredKeys as $key) {
        if (!isset($_SESSION[$key])) {
            return null;
        }
    }

    return buildUserObject([
        'id' => (int)$_SESSION['user_id'],
        'name' => (string)$_SESSION['user_name'],
        'email' => (string)$_SESSION['user_email'],
        'username' => (string)$_SESSION['user_username'],
        'role' => (string)$_SESSION['user_role'],
    ]);
}

function isLoggedIn(): bool
{
    return currentUser() instanceof User;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('flash_error', 'Duhet të kyçeni fillimisht.');
        redirect($GLOBALS['base_url'] . '/login.php');
    }
}

function requireRole(string $role): void
{
    requireLogin();

    $user = currentUser();

    if (!$user || $user->getRole() !== $role) {
        setFlash('flash_error', 'Nuk keni qasje në këtë faqe.');
        redirect($GLOBALS['base_url'] . '/index.php');
    }
}