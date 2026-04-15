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

header('Location: ' . $GLOBALS['base_url'] . '/pages/contact.php');
exit;
    }

$pageTitle = 'Kontakti';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
?>

<section class="contact-page">
    <div class="contact-page-header">
        <span class="contact-badge">Na Kontaktoni</span>
        <h1>Kontaktoni për Çdo Pyetje</h1>
    </div>

    <div class="contact-layout-grid">
        <div class="contact-col contact-left">
            <h2>Na Kontaktoni</h2>
            <p class="contact-left-text">Na shkruani për pyetje rreth fluturimeve, rezervimeve apo bashkëpunimeve.</p>

            <div class="contact-info-item">
                <div class="contact-icon-box">⚲</div>
                <div>
                    <h3>Zyra</h3>
                    <p>Rr. "Iliria" Nr. 27, Ferizaj, Kosovë</p>
                </div>
            </div>

            <div class="contact-info-item">
                <div class="contact-icon-box">✆</div>
                <div>
                    <h3>Mobile</h3>
                    <p><?= e($GLOBALS['support_phone']) ?></p>
                </div>
            </div>

            <div class="contact-info-item">
                <div class="contact-icon-box">✉</div>
                <div>
                    <h3>Email</h3>
                    <p><?= e($GLOBALS['support_email']) ?></p>
                </div>
            </div>
        </div>