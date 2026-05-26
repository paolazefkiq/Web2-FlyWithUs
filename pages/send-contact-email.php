<?php

require_once __DIR__ . '/../includes/config.php';

$contactPageUrl = $GLOBALS['base_url'] . '/pages/contact.php';
$currentUser = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($contactPageUrl);
}

if (!$currentUser) {
    setFlash('contact_error', 'Ju duhet te kyceni per te na shkruar.');
    redirect($contactPageUrl);
}

if (!$currentUser->canSendContactMessage()) {
    setFlash('contact_error', 'Mesazhet e klienteve menaxhohen ne dashboard-in e administratorit.');
    redirect($contactPageUrl);
}

$old = [
    'subject' => trim($_POST['subject'] ?? ''),
    'message' => trim($_POST['message'] ?? ''),
];

$errors = [
    'subject' => '',
    'message' => '',
];

if ($old['subject'] === '') {
    $errors['subject'] = 'Ju lutem plotesoni subjektin.';
} elseif (mb_strlen($old['subject']) < 3) {
    $errors['subject'] = 'Subjekti duhet te kete se paku 3 karaktere.';
}

if ($old['message'] === '') {
    $errors['message'] = 'Ju lutem shkruani mesazhin.';
} elseif (mb_strlen($old['message']) < 5) {
    $errors['message'] = 'Mesazhi duhet te kete se paku 5 karaktere.';
}

if (array_filter($errors)) {
    $_SESSION['contact_old'] = $old;
    $_SESSION['contact_errors'] = $errors;
    redirect($contactPageUrl);
}