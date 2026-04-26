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

if ($old['email'] === '') {
    $errors['email'] = 'Ju lutem plotësoni email-in.';
} elseif (!preg_match($emailPattern, $old['email'])) {
    $errors['email'] = 'Email nuk është valid.';
}

    if ($old['from'] === '') {
        $errors['from'] = 'Zgjidh qytetin e nisjes.';
    }

    if ($old['to'] === '') {
        $errors['to'] = 'Zgjidh destinacionin.';
    } elseif ($old['from'] !== '' && (!isset($flightMatrix[$old['from']]) || !isset($flightMatrix[$old['from']][$old['to']]))) {
        $errors['to'] = 'Zgjedhja e fluturimit nuk është valide.';
    }

    if ($old['depart'] === '') {
        $errors['depart'] = 'Data e nisjes është e detyrueshme.';
    }

    if ($old['return'] !== '' && $old['depart'] !== '' && $old['return'] < $old['depart']) {
        $errors['return'] = 'Data e kthimit nuk mund të jetë para nisjes.';
    }

    $passengersNumber = (int)$old['passengers'];
    if ($passengersNumber < 1 || $passengersNumber > 5) {
        $errors['passengers'] = 'Numri i pasagjerëve duhet të jetë nga 1 deri në 5.';
    }

    if (!array_filter($errors)) {
        $basePrice = $flightMatrix[$old['from']][$old['to']];
        $total = $basePrice * $passengersNumber;

        if ($old['return'] !== '') {
            $total *= 2;
        }

        $_SESSION['last_booking'] = [
            'name' => $old['name'],
            'route' => $old['from'] . ' - ' . $old['to'],
            'total' => $total,
            'date' => $old['depart']
        ];

        setcookie('preferred_city', $old['to'], time() + 60 * 60 * 24 * 30, '/');

$_SESSION['popup_success'] = "
    Rezervimi është bërë me sukses, <strong>" . e($old['name']) . "</strong>!<br><br>
    Ju do të udhëtoni nga <strong>" . e($old['from']) . "</strong> drejt <strong>" . e($old['to']) . "</strong>
    për <strong>" . e($old['passengers']) . "</strong> pasagjer(ë) më datën
    <span style='white-space: nowrap;'><strong>" . e($old['depart']) . "</strong></span>" .
    ($old['return'] !== ''
        ? " dhe kthim më <span style='white-space: nowrap;'><strong>" . e($old['return']) . "</strong></span>."
        : ".") .
    "<br><br>
    Ne do t'ju kontaktojmë së shpejti në <strong>" . e($old['email']) . "</strong>.<br>
    Totali i rezervimit: <strong>$" . e((string)$total) . "</strong>.
";

header("Location: " . $_SERVER['PHP_SELF']);
exit;
    }
}
$hasErrors = !empty(array_filter($errors));
?>

<header class="hero" id="hero">
    <div class="hero-overlay">
        <h1 class="hero-title">Fly With Us</h1>
        <p class="hero-subtitle">
            Zbuloni qytete të bukura në të gjithë botën me bileta fleksibile dhe çmime të shkëlqyera.
        </p>
        <a href="#booking" class="btn-primary">Rezervo Tani</a>
    </div>
</header>

<main>
    <?php if ($flashSuccess): ?>
        <div class="alert success"><?= e($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert error"><?= e($flashError) ?></div>
    <?php endif; ?>

    <section class="info-strip">
        <div>
    <strong>Cookie preference:</strong>
    <?php if ($preferredCity !== ''): ?>
        Destinacioni juaj i fundit i ruajtur është <strong><?= e($preferredCity) ?></strong>.
    <?php else: ?>
        Nuk ka ende destinacion të ruajtur në cookie.
    <?php endif; ?>
</div>

             <?php if (isLoggedIn()): ?>
            <div><strong>Session:</strong> <?= e(currentUser()->getGreeting()) ?></div>
        <?php else: ?>
            <div>
    <strong>Demo login:</strong>
    customer@flywithus.com ose customer1 / Customer123
    ose
    admin@flywithus.com ose admin1 / Admin123
            </div>
        <?php endif; ?>
    </section>

    <section id="booking" class="booking-section">
        <div class="booking-container">
            <h2>Rezervo Fluturimin Tuaj</h2>
            <p class="section-subtitle">
                Plotëso detajet më poshtë dhe ne do të përgatisim ofertën më të mirë për ju.
            </p>

            <form id="bookingForm" class="booking-form" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>
                <div class="form-row">
                    <div class="form-group">
    <label for="name">Emri Juaj</label>
    <input type="text" id="name" name="name" placeholder="Filan Fisteku" value="<?= e($old['name']) ?>">
    <?php if ($errors['name']): ?>
        <div class="field-error"><?= e($errors['name']) ?></div>
    <?php endif; ?>
</div>

         <div class="form-group">
    <label for="email">Email</label>
    <input type="text" id="email" name="email" placeholder="filanfisteku@gmail.com" value="<?= e($old['email']) ?>">
    <?php if ($errors['email']): ?>
        <div class="field-error"><?= e($errors['email']) ?></div>
    <?php endif; ?>
</div>
</div>

                <div class="form-row">
                    <div class="form-group">
    <label for="from">Nga</label>
<select id="from" name="from">
    <option value="">Zgjidh qytetin</option>
    <?php foreach (array_keys($flightMatrix) as $fromCity): ?>
        <option value="<?= e($fromCity) ?>" <?= $old['from'] === $fromCity ? 'selected' : '' ?>>
            <?= e($fromCity) ?>
        </option>
    <?php endforeach; ?>
</select>

<?php if ($errors['from']): ?>
        <div class="field-error"><?= e($errors['from']) ?></div>
    <?php endif; ?>
</div>

                    <div class="form-group">
    <label for="to">Deri në</label>
<select id="to" name="to">
    <option value="">Zgjidh qytetin</option>
    <?php foreach ($sortedDestinations as $destination): ?>
        <option value="<?= e($destination['city']) ?>" <?= $old['to'] === $destination['city'] ? 'selected' : '' ?>>
            <?= e($destination['city']) ?>
        </option>
    <?php endforeach; ?>
</select>

         <?php if ($errors['to']): ?>
    <div class="field-error"><?= e($errors['to']) ?></div>
<?php endif; ?>
</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
    <label for="depart">Data e Nisjes</label>
    <input type="date" id="depart" name="depart" value="<?= e($old['depart']) ?>">
    <?php if ($errors['depart']): ?>
        <div class="field-error"><?= e($errors['depart']) ?></div>
    <?php endif; ?>
</div>

                    <div class="form-group">
    <label for="return">Data e Kthimit</label>
    <input type="date" id="return" name="return" value="<?= e($old['return']) ?>">
    <?php if ($errors['return']): ?>
        <div class="field-error"><?= e($errors['return']) ?></div>
    <?php endif; ?>
</div>

        <div class="form-group">
    <label for="passengers">Pasagjerët</label>
    <select id="passengers" name="passengers">
        <option value="1" <?= $old['passengers'] === '1' ? 'selected' : '' ?>>1</option>
        <option value="2" <?= $old['passengers'] === '2' ? 'selected' : '' ?>>2</option>
        <option value="3" <?= $old['passengers'] === '3' ? 'selected' : '' ?>>3</option>
        <option value="4" <?= $old['passengers'] === '4' ? 'selected' : '' ?>>4</option>
        <option value="5" <?= $old['passengers'] === '5' ? 'selected' : '' ?>>5</option>
    </select>
    <?php if ($errors['passengers']): ?>
        <div class="field-error"><?= e($errors['passengers']) ?></div>
    <?php endif; ?>
    </div>
                </div>

                 <div class="booking-button-container">
                    <button type="submit" class="btn-primary btn-full">Rezervo Tani</button>
                    <div id="livePrice">Çmimi: $0</div>
                </div>
            </form>

            <p id="bookingSuccess" class="booking-success hidden"></p>
        </div>

        <div class="booking-info">
            <h3>Pse të rezervoni me ne?</h3><br>
            <ul class="booking-info">
                <li>✔ Mbështetje 24/7 për klientët</li>
                <li>✔ Pa tarifa të fshehura</li>
                <li>✔ Ndryshime fleksibile për shumicën e biletave</li>
                <li>✔ E besuar nga mijëra udhëtarë</li>
            </ul>
        </div>
    </section>
    <section class="destinations" id="destinations">
        <h2>Destinacionet Popullore</h2>
        <p class="section-subtitle">
            Qytete të përzgjedhura me fluturime ditore me çmime të jashtëzakonshme.
        </p>

        <div class="cards">
            <article class="card">
                <img src="<?= $GLOBALS['base_url'] ?>/assets/img/destination1.jpg" alt="New York skyline">
                <div class="card-content">
                    <h3>New York</h3>
                    <p>
                        Përjeto qytetin që kurrë nuk fle. I përsosur për blerje, shfaqje dhe pamje të paharrueshme.<br>
                        <span>Duke filluar nga <strong>$399</strong></span>
                    </p>
                    <button type="button" class="card-btn" data-city="New York">Shiko Oferta</button>
                </div>
            </article>

            <article class="card">
                <img src="<?= $GLOBALS['base_url'] ?>/assets/img/destination2.avif" alt="Paris Eiffel Tower">
                <div class="card-content">
                    <h3>Paris</h3>
                    <p>
                        Ecje romantike pranë lumit Seine, muzeume me famë botërore dhe ushqim i shijshëm në çdo cep.<br>
                        <span>Duke filluar nga <strong>$299</strong></span>
                    </p>
                    <button type="button" class="card-btn" data-city="Paris">Shiko Oferta</button>
                </div>
            </article>

            <article class="card">
                <img src="<?= $GLOBALS['base_url'] ?>/assets/img/destination3.jpg" alt="Tokyo night lights">
                <div class="card-content">
                    <h3>Tokyo</h3>
                    <p>
                        Një përzierje e përsosur e traditës dhe teknologjisë. Tempuj, drita neon dhe ushqim mahnitës.<br>
                        <span>Duke filluar nga <strong>$749</strong></span>
                    </p>
                    <button type="button" class="card-btn" data-city="Tokyo">Shiko Oferta</button>
                </div>
            </article>
        </div>
    </section>
</main>

<div id="successPopup" class="popup-overlay" style="<?= $popupSuccess ? 'display: flex;' : 'display: none;' ?>">
    <div class="popup-box">
        <h2>Rezervimi u krye me sukses!</h2>
        <p id="popupMessage"><?= $popupSuccess ?></p>
        <button id="closePopup" type="button">Mbylle</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const hasErrors = <?= $hasErrors ? 'true' : 'false' ?>;

    if (hasErrors) {
        const bookingSection = document.getElementById("booking");
        if (bookingSection) {
            bookingSection.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
?>