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