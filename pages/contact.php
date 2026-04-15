<?php

require_once __DIR__ . '/../includes/config.php';

$old = $_SESSION['contact_old'] ?? [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];
unset($_SESSION['contact_old']);

$errors = $_SESSION['contact_errors'] ?? [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];
unset($_SESSION['contact_errors']);

$popupSuccess = $_SESSION['contact_popup_success'] ?? '';
unset($_SESSION['contact_popup_success']);

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

    if ($old['email'] === '') {
        $errors['email'] = 'Ju lutem plotësoni email-in.';
    } elseif (!preg_match($emailPattern, $old['email'])) {
        $errors['email'] = 'Email nuk është valid.';
    }

    if ($old['subject'] === '') {
        $errors['subject'] = 'Ju lutem plotësoni subjektin.';
    } elseif (mb_strlen($old['subject']) < 3) {
        $errors['subject'] = 'Subjekti duhet të ketë së paku 3 karaktere.';
    }

    if ($old['message'] === '') {
        $errors['message'] = 'Ju lutem shkruani mesazhin.';
    } elseif (mb_strlen($old['message']) < 5) {
        $errors['message'] = 'Mesazhi duhet të ketë së paku 5 karaktere.';
    }

    if (array_filter($errors)) {
    $_SESSION['contact_old'] = $old;
    $_SESSION['contact_errors'] = $errors;
    header('Location: ' . $GLOBALS['base_url'] . '/pages/contact.php');
    exit;
}

$_SESSION['contact_message'] = [
    'name' => $old['name'],
    'email' => $old['email'],
    'subject' => $old['subject'],
    'message' => $old['message'],
    'sent_at' => date('Y-m-d H:i:s')
];

$_SESSION['contact_popup_success'] = "
    Mesazhi u dërgua me sukses!<br><br>
    <strong>Emri:</strong> " . e($old['name']) . "<br>
    <strong>Email:</strong> " . e($old['email']) . "<br>
    <strong>Subjekti:</strong> " . e($old['subject']) . "
";
?>