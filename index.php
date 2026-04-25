<?php
$pageTitle = 'Ballina';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';

// Marrja e te dhenave
$sortedDestinations = sortDestinationsByPrice($destinations);
$flashSuccess = getFlash('flash_success');
$flashError = getFlash('flash_error');
$popupSuccess = $_SESSION['popup_success'] ?? '';
unset($_SESSION['popup_success']);
$preferredCity = $_COOKIE['preferred_city'] ?? '';

$old = [
    'name' => '',
    'email' => '',
    'from' => '',
    'to' => '',
    'depart' => '',
    'return' => '',
    'passengers' => '1'
];
?>