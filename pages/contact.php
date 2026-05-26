<?php

require_once __DIR__ . '/../includes/config.php';

$currentUser = currentUser();
$canSubmitMessage = $currentUser && $currentUser->canSendContactMessage();
$contactPageUrl = $GLOBALS['base_url'] . '/pages/contact.php';
$contactSubmitUrl = $GLOBALS['base_url'] . '/pages/send-contact-email.php';
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
    'message' => '',
];
unset($_SESSION['contact_errors']);

$contactError = getFlash('contact_error');
$popupSuccess = $_SESSION['contact_popup_success'] ?? '';
unset($_SESSION['contact_popup_success']);

$pageTitle = 'Kontakti';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
?>

<section class="contact-page">
    <div class="contact-page-header">
        <span class="contact-badge">Kontakti</span>
        <h1>Na kontaktoni</h1>
    </div>

    <div class="contact-layout-grid">
        <div class="contact-col contact-left">
            <h2>Jemi ketu per ju</h2>
            <p class="contact-left-text">Na shkruani per pyetje rreth fluturimeve, rezervimeve ose ofertave.</p>

            <div class="contact-info-item">
                <div class="contact-icon-box">&#9992;</div>
                <div>
                    <h3>Zyra</h3>
                    <p>Rr. "Iliria" Nr. 27, Ferizaj, Kosove</p>
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
                <form class="contact-form-box" method="POST" action="<?= e($contactSubmitUrl) ?>" novalidate>
                    <?php if ($contactError): ?>
                        <div class="alert error"><?= e($contactError) ?></div>
                    <?php endif; ?>

                    <div class="contact-account-box">
                        Po kontaktoni si:<br>
                        <strong><?= e($currentUser->getEmail()) ?></strong>
                    </div>

                    <div class="float-group">
                        <span class="input-icon">&#9998;</span>
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

                    <button class="btn-primary btn-block" type="submit">Dergo mesazhin</button>
                </form>
            <?php elseif ($currentUser): ?>
                <div class="contact-form-box contact-form-box--notice">
                    <?php if ($contactError): ?>
                        <div class="alert error"><?= e($contactError) ?></div>
                    <?php endif; ?>

                    <p class="contact-notice-text">
                        Mesazhet e klienteve menaxhohen ne dashboard-in e administratorit.
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
                        <p>Kyquni per te na shkruar nga llogaria juaj.</p>
                        <div class="contact-notice-actions">
                            <a class="btn-primary btn-block" href="<?= e($loginUrl) ?>">Kycu</a>
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
        <p class="popup-message" id="contactPopupMessage"><?= e($popupSuccess) ?></p>
        <div class="popup-actions">
            <button id="contactClose" class="btn-secondary" type="button">Mbylle</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
