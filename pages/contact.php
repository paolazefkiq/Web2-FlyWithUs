<?php

require_once __DIR__ . '/../includes/config.php';

$currentUser = currentUser();
$canSubmitMessage = $currentUser && $currentUser->canSendContactMessage();
$contactPageUrl = $GLOBALS['base_url'] . '/pages/contact.php';
$loginUrl = $GLOBALS['base_url'] . '/login.php?redirect=' . urlencode($contactPageUrl);
$messagesUrl = $GLOBALS['base_url'] . '/pages/admin-dashboard.php#messages';

$defaultValues = [
    'subject' => '',
    'message' => '',
];

$old = $_SESSION['contact_old'] ?? $defaultValues;
unset($_SESSION['contact_old']);

$errors = $_SESSION['contact_errors'] ?? [
    'subject' => '',
    'message' => ''
];
unset($_SESSION['contact_errors']);

$contactError = getFlash('contact_error');
$popupSuccess = $_SESSION['contact_popup_success'] ?? '';
unset($_SESSION['contact_popup_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$currentUser) {
        setFlash('contact_error', 'Ju duhet të kyçeni për të na shkruar.');
        redirect($contactPageUrl);
    }

    if (!$currentUser->canSendContactMessage()) {
        setFlash('contact_error', 'Mesazhet e klientëve menaxhohen në dashboard-in e administratorit.');
        redirect($contactPageUrl);
    }

    $old['subject'] = trim($_POST['subject'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');

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

        $fromAddress = filter_var($GLOBALS['support_email'], FILTER_VALIDATE_EMAIL)
            ? $GLOBALS['support_email']
            : 'noreply@localhost';
        $replyToAddress = filter_var($currentUser->getEmail(), FILTER_VALIDATE_EMAIL)
            ? $currentUser->getEmail()
            : $fromAddress;
        $mailSubjectText = str_replace(["\r", "\n"], ' ', $old['subject']);
        $mailSubject = 'Kontakt: ' . $mailSubjectText;
        $mailBody = "Mesazh i ri nga forma e kontaktit:\n\n"
            . "Emri: " . $currentUser->getName() . "\n"
            . "Email: " . $currentUser->getEmail() . "\n"
            . "Subjekti: " . $old['subject'] . "\n\n"
            . "Mesazhi:\n" . $old['message'] . "\n";
        $mailHeaders = [
            'From: ' . $fromAddress,
            'Reply-To: ' . $replyToAddress,
            'Content-Type: text/plain; charset=UTF-8',
        ];
        $encodedSubject = '=?UTF-8?B?' . base64_encode($mailSubject) . '?=';
        $previousSendmailFrom = ini_get('sendmail_from');
        ini_set('sendmail_from', $fromAddress);
        error_clear_last();
        $mailSent = mail(
            $GLOBALS['support_email'],
            $encodedSubject,
            $mailBody,
            implode("\r\n", $mailHeaders)
        );

        if ($previousSendmailFrom !== false) {
            ini_set('sendmail_from', (string)$previousSendmailFrom);
        }

        $_SESSION['contact_popup_success'] = $mailSent
            ? 'Mesazhi u ruajt dhe u dergua me email. Do t\'ju kontaktojme sa me shpejt.'
            : (
                PHP_OS_FAMILY === 'Windows' && trim((string)ini_get('SMTP')) === 'localhost'
                    ? 'Mesazhi u ruajt me sukses. SMTP lokal nuk eshte i konfiguruar ende, prandaj email-i nuk u dergua.'
                    : 'Mesazhi u ruajt me sukses, por dergimi i email-it nuk u konfirmua.'
            );

        redirect($contactPageUrl);
    } catch (PDOException $exception) {
        $_SESSION['contact_old'] = $old;
        $_SESSION['contact_errors'] = $errors;
        setFlash('contact_error', 'Ndodhi një gabim. Ju lutemi provoni përsëri më vonë.');
        redirect($contactPageUrl);
    }
}

$pageTitle = 'Kontakti';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
?>

<section class="contact-page">
    <div class="contact-page-header">
        <span class="contact-badge">Kontakti</span>
        <h1>Na Kontaktoni</h1>
    </div>

    <div class="contact-layout-grid">
        <div class="contact-col contact-left">
            <h2>Jemi këtu për ju</h2>
            <p class="contact-left-text">Na shkruani për pyetje rreth fluturimeve, rezervimeve apo ofertave.</p>

            <div class="contact-info-item">
                <div class="contact-icon-box">&#9992;</div>
                <div>
                    <h3>Zyra</h3>
                    <p>Rr. "Iliria" Nr. 27, Ferizaj, Kosovë</p>
                </div>
            </div>

            <div class="contact-info-item">
                <div class="contact-icon-box">&#9742;</div>
                <div>
                    <h3>Telefoni</h3>
                    <p><?= e($GLOBALS['support_phone']) ?></p>
                </div>
            </div>

            <div class="contact-info-item">
                <div class="contact-icon-box">&#9993;</div>
                <div>
                    <h3>Email</h3>
                    <p><?= e($GLOBALS['support_email']) ?></p>
                </div>
            </div>
        </div>

        <div class="contact-col contact-map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d23456.789012345!2d21.1521!3d42.3833!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x13545abcdef12345%3A0xabcdef123456789!2sFerizaj%2C%20Kosovo!5e0!3m2!1sen!2s!4v1700000000000" width="100%" height="450" style="border:0;" loading="lazy"></iframe>
        </div>

         <div class="contact-col contact-right">
            <?php if ($canSubmitMessage): ?>
            <form class="contact-form-box" method="POST" action="<?= $contactPageUrl ?>" novalidate> 
                    <?php if ($contactError): ?>
                        <div class="alert error"><?= e($contactError) ?></div>
                    <?php endif; ?>

                    <div class="contact-account-box">
                        Po kontaktoni si:<br>
                        <strong><?= e($currentUser->getEmail()) ?></strong>
                         </div

 <div class="float-group">
    <span class="input-icon">&#9998;<</span>
    <input
        type="text"
        id="c-subject"
        name="subject"
        placeholder=" "
        value="<?= e($old['subject']) ?>"
        class="<?= $errors['subject'] ? 'input-error' : '' ?>"
    >
    <label for="c-subject">Subjekti</label>
    <?php if ($errors['subject']): ?>
        <div class="field-error"><?= e($errors['subject']) ?></div>
    <?php endif; ?>
</div>

 <div class="float-group">
    <span class="input-icon">&#128172;</span>
    <textarea
        id="c-message"
        name="message"
        placeholder=" "
        class="<?= $errors['message'] ? 'input-error' : '' ?>"
    ><?= e($old['message']) ?></textarea>
    <label for="c-message">Mesazhi</label>
    <?php if ($errors['message']): ?>
        <div class="field-error"><?= e($errors['message']) ?></div>
    <?php endif; ?>
</div>

    <button class="button-modern" type="submit">Dërgo Mesazh</button>
            </form>
         <?php elseif ($currentUser): ?>
                <div class="contact-form-box contact-form-box--notice">
                    <?php if ($contactError): ?>
                        <div class="alert error"><?= e($contactError) ?></div>
                    <?php endif; ?>

                    <p class="contact-notice-text">
                        Mesazhet e klientëve menaxhohen në dashboard-in e administratorit.
                    </p>
                    <div class="contact-notice-actions">
                        <a class="btn-primary btn-block" href="<?= e($messagesUrl) ?>">Shiko mesazhet</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="contact-form-box">
                    <?php if ($contactError): ?>
                        <div class="alert error"><?= e($contactError) ?></div>
                    <?php endif; ?>

                    <div class="contact-notice-box">
                        <p>Kyçuni për të na shkruar nga llogaria juaj.</p>
                        <div class="contact-notice-actions">
                            <a class="btn-primary btn-block" href="<?= e($loginUrl) ?>">Kyçu</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<div id="contactPopup" class="popup-overlay" style="<?= $popupSuccess ? 'display: flex;' : 'display: none;' ?>">
    <div class="popup-box">
        <h2>Faleminderit!</h2>
        <p id="contactPopupMessage"><?= $popupSuccess ?></p>
          <p class="popup-message" id="contactPopupMessage"><?= e($popupSuccess) ?></p>
        <div class="popup-actions">
        <button id="contactClose" type="button">Mbylle</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>