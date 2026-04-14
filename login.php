<?php
require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) {
    $user = currentUser();
    redirect($user->getRole() === 'admin'
    ? $GLOBALS['base_url'] . '/pages/admin-dashboard.php'
    : $GLOBALS['base_url'] . '/pages/customer-dashboard.php'
);
}