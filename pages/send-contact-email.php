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

$sendProjectContactEmail = static function (
    string $subject,
    string $message,
    User $currentUser
): array {
    $smtpUsername = trim((string)($GLOBALS['smtp_username'] ?? ''));
    $smtpPassword = trim((string)($GLOBALS['smtp_password'] ?? ''));
    $smtpFromEmail = trim((string)($GLOBALS['smtp_from_email'] ?? ''));
    $smtpFromName = trim((string)($GLOBALS['smtp_from_name'] ?? ($GLOBALS['site_name'] ?? 'Fly With Us')));
    $smtpHost = trim((string)($GLOBALS['smtp_host'] ?? 'smtp.gmail.com'));
    $smtpPort = (int)($GLOBALS['smtp_port'] ?? 587);
    $smtpEncryption = trim((string)($GLOBALS['smtp_encryption'] ?? 'tls'));
    $recipientEmail = trim((string)($GLOBALS['contact_inbox_email'] ?? ''));

    if ($smtpFromEmail === '' && $smtpUsername !== '') {
        $smtpFromEmail = $smtpUsername;
    }

    if ($recipientEmail === '') {
        $recipientEmail = trim((string)($GLOBALS['support_email'] ?? ''));
    }

    $vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';

    if (!is_file($vendorAutoload)) {
        return [
            'sent' => false,
            'message' => 'Mesazhi u ruajt me sukses, por PHPMailer nuk eshte i instaluar.',
        ];
    }

    if (
        $smtpUsername === '' ||
        $smtpPassword === '' ||
        $smtpFromEmail === '' ||
        $recipientEmail === ''
    ) {
        return [
            'sent' => false,
            'message' => 'Mesazhi u ruajt me sukses, por email-i nuk eshte konfiguruar ende.',
        ];
    }

    require_once $vendorAutoload;

    if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
        return [
            'sent' => false,
            'message' => 'Mesazhi u ruajt me sukses, por PHPMailer nuk u ngarkua si duhet.',
        ];
    }

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = $smtpEncryption;
        $mail->Port = $smtpPort;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($smtpFromEmail, $smtpFromName);
        $mail->addAddress($recipientEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Kontakt: ' . $subject;
        $mail->Body =
            '<h2>Mesazh i ri nga forma e kontaktit</h2>'
            . '<p><strong>Emri:</strong> ' . e($currentUser->getName()) . '</p>'
            . '<p><strong>Email:</strong> ' . e($currentUser->getEmail()) . '</p>'
            . '<p><strong>Subjekti:</strong> ' . e($subject) . '</p>'
            . '<p><strong>Mesazhi:</strong><br>' . nl2br(e($message)) . '</p>';
        $mail->AltBody =
            "Mesazh i ri nga forma e kontaktit\n\n"
            . "Emri: " . $currentUser->getName() . "\n"
            . "Email: " . $currentUser->getEmail() . "\n"
            . "Subjekti: " . $subject . "\n\n"
            . "Mesazhi:\n" . $message . "\n";
        $mail->send();

        return [
            'sent' => true,
            'message' => 'Mesazhi u ruajt dhe u dergua me email.',
        ];
    } catch (\Throwable $exception) {
        return [
            'sent' => false,
            'message' => 'Mesazhi u ruajt me sukses, por dergimi i email-it deshtoi.',
        ];
    }
};
