<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

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

try {
    $pdo = getPDO();
    $insertStatement = $pdo->prepare(
        'INSERT INTO contact_messages (user_id, name, email, subject, message)
         VALUES (:user_id, :name, :email, :subject, :message)'
    );

    $insertStatement->bindValue(':user_id', $currentUser->getId(), PDO::PARAM_INT);
    $insertStatement->bindValue(':name', $currentUser->getName(), PDO::PARAM_STR);
    $insertStatement->bindValue(':email', $currentUser->getEmail(), PDO::PARAM_STR);
    $insertStatement->bindValue(':subject', $old['subject'], PDO::PARAM_STR);
    $insertStatement->bindValue(':message', $old['message'], PDO::PARAM_STR);
    $insertStatement->execute();

    $smtpUsername = trim((string)($GLOBALS['smtp_username'] ?? ''));
    $smtpPassword = trim((string)($GLOBALS['smtp_password'] ?? ''));
    $smtpFromEmail = trim((string)($GLOBALS['smtp_from_email'] ?? ''));
    $recipientEmail = trim((string)($GLOBALS['contact_inbox_email'] ?? ''));

    if (
        $smtpUsername === '' ||
        $smtpPassword === '' ||
        $smtpFromEmail === '' ||
        $recipientEmail === ''
    ) {
        $_SESSION['contact_popup_success'] = 'Mesazhi u ruajt me sukses, por email-i nuk eshte konfiguruar ende.';
        redirect($contactPageUrl);
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = (string)($GLOBALS['smtp_host'] ?? 'smtp.gmail.com');
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUsername;
    $mail->Password = $smtpPassword;
    $mail->SMTPSecure = (string)($GLOBALS['smtp_encryption'] ?? 'tls');
    $mail->Port = (int)($GLOBALS['smtp_port'] ?? 587);
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($smtpFromEmail, (string)($GLOBALS['smtp_from_name'] ?? 'Fly With Us'));
    $mail->addAddress($recipientEmail);
    $mail->addReplyTo($currentUser->getEmail(), $currentUser->getName());
    $mail->isHTML(true);
    $mail->Subject = 'Kontakt: ' . $old['subject'];
    $mail->Body =
        '<h2>Mesazh i ri nga forma e kontaktit</h2>'
        . '<p><strong>Emri:</strong> ' . e($currentUser->getName()) . '</p>'
        . '<p><strong>Email:</strong> ' . e($currentUser->getEmail()) . '</p>'
        . '<p><strong>Subjekti:</strong> ' . e($old['subject']) . '</p>'
        . '<p><strong>Mesazhi:</strong><br>' . nl2br(e($old['message'])) . '</p>';

    $mail->send();

    $_SESSION['contact_popup_success'] = 'Mesazhi u ruajt dhe u dergua me email. Do t\'ju kontaktojme sa me shpejt.';
    redirect($contactPageUrl);
} catch (Exception | PDOException $exception) {
    $_SESSION['contact_old'] = $old;
    $_SESSION['contact_errors'] = $errors;
    $_SESSION['contact_popup_success'] = 'Mesazhi u ruajt me sukses, por dergimi i email-it deshtoi.';
    redirect($contactPageUrl);
}

