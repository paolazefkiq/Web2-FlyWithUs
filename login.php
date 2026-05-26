<?php
require_once __DIR__ . '/includes/config.php';

$redirectAfterLogin = sanitizeInternalRedirect($_GET['redirect'] ?? '');

if (isLoggedIn()) {
    $user = currentUser();

    if ($user->getRole() === 'customer' && $redirectAfterLogin !== '') {
        redirect($redirectAfterLogin);
    }
 redirect(
        $user->getRole() === 'admin'
            ? $GLOBALS['base_url'] . '/pages/admin-dashboard.php'
            : $GLOBALS['base_url'] . '/pages/customer-dashboard.php'
    );
}
$old = [
    'login' => '',
];

$errors = [
    'login' => '',
    'password' => '',
    'general' => '',
];
