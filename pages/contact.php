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

        <div class="contact-col contact-map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d23456.789012345!2d21.1521!3d42.3833!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x13545abcdef12345%3A0xabcdef123456789!2sFerizaj%2C%20Kosovo!5e0!3m2!1sen!2s!4v1700000000000" width="100%" height="450" style="border:0;" loading="lazy"></iframe>
        </div>

         <div class="contact-col contact-right">
            <form class="contact-form-box" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>
                <div class="float-group">
    <span class="input-icon">𖤘</span>
    <input
        type="text"
        id="c-name"
        name="name"
        placeholder=" "
        value="<?= e($old['name']) ?>"
        class="<?= $errors['name'] ? 'input-error' : '' ?>"
    >
    <label for="c-name">Emri Juaj</label>
    <?php if ($errors['name']): ?>
        <div class="field-error"><?= e($errors['name']) ?></div>
    <?php endif; ?>
</div> 

 <div class="float-group">
    <span class="input-icon">✉</span>
    <input
        type="text"
        id="c-email"
        name="email"
        placeholder=" "
        value="<?= e($old['email']) ?>"
        class="<?= $errors['email'] ? 'input-error' : '' ?>"
    >
    <label for="c-email">Email Juaj</label>
    <?php if ($errors['email']): ?>
        <div class="field-error"><?= e($errors['email']) ?></div>
    <?php endif; ?>
</div>

 <div class="float-group">
    <span class="input-icon">✎</span>
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
    <span class="input-icon">🗨</span>
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
        </div>
    </div>
</section>

<div id="contactPopup" class="popup-overlay" style="<?= $popupSuccess ? 'display: flex;' : 'display: none;' ?>">
    <div class="popup-box">
        <h2>Mesazhi u dërgua me sukses!</h2>
        <p id="contactPopupMessage"><?= $popupSuccess ?></p>
        <button id="contactClose" type="button">Mbylle</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const contactPopup = document.getElementById("contactPopup");
    const contactClose = document.getElementById("contactClose");

    if (contactPopup && contactClose) {
        contactClose.addEventListener("click", () => {
            contactPopup.style.display = "none";
        });

        contactPopup.addEventListener("click", (e) => {
            if (e.target === contactPopup) {
                contactPopup.style.display = "none";
            }
        });

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                contactPopup.style.display = "none";
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>