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

$errors = [
    'name' => '',
    'email' => '',
    'from' => '',
    'to' => '',
    'depart' => '',
    'return' => '',
    'passengers' => ''
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $value) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    $namePattern = '/^[A-Za-zÇçËëÁÉÍÓÚáéíóúÄÖÜäöü\s]{2,50}$/u';
    $emailPattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

    if ($old['name'] === '') {
    $errors['name'] = 'Ju lutem plotësoni emrin.';
} elseif (!preg_match($namePattern, $old['name'])) {
    $errors['name'] = 'Emri duhet të ketë vetëm shkronja dhe së paku 2 karaktere.';
}
?>