<?php
require_once __DIR__ . '/../includes/config.php';

$currentUser = currentUser();
$loadError = '';
$bookingError = '';
$destinations = [];
$destinationsById = [];
$bookingDestinations = [];
$bookingPrices = [];
$originCities = [];
$originCitiesById = [];
$bookingOriginCities = [];
$popupSuccess = $_SESSION['popup_success'] ?? '';
unset($_SESSION['popup_success']);

$flashSuccess = getFlash('flash_success');
$flashError = getFlash('flash_error');
$today = date('Y-m-d');
$defaultOfferImage = $GLOBALS['base_url'] . '/assets/img/airplane-bg.jpg';
$bookingDefaultPanel = [
    'eyebrow' => 'Fly With Us',
    'title' => 'Udhëtimi juaj fillon këtu',
    'subtitle' => 'Zgjidhni destinacionin dhe nisuni drejt aventurës suaj të radhës.',
    'description' => 'Qytete të reja, fluturime të qarta dhe një rezervim i thjeshtë kur të jeni gati.',
    'imageUrl' => $defaultOfferImage,
];

$old = [
    'destination_id' => '',
    'origin_city_id' => '',
    'depart' => '',
    'return' => '',
    'passengers' => '1',
];

$errors = [
    'destination_id' => '',
    'origin_city_id' => '',
    'depart' => '',
    'return' => '',
    'passengers' => '',
];

try {
    $pdo = getPDO();

    $destinationsStatement = $pdo->prepare(
        'SELECT d.id, d.city, d.country, d.description, d.image_path, MIN(r.price) AS min_price
         FROM destinations d
         INNER JOIN routes r ON r.destination_id = d.id
         GROUP BY d.id, d.city, d.country, d.description, d.image_path
         ORDER BY d.city ASC'
    );
    $destinationsStatement->execute();

    foreach ($destinationsStatement->fetchAll() as $destinationRow) {
        $destinationId = (int)$destinationRow['id'];
        $imagePath = buildDestinationImagePath($destinationRow);
        $imageUrl = $GLOBALS['base_url'] . '/' . ltrim($imagePath, '/');

        $destinationRow['id'] = $destinationId;
        $destinations[] = $destinationRow;
        $destinationsById[$destinationId] = $destinationRow;
        $bookingDestinations[(string)$destinationId] = [
            'id' => $destinationId,
            'city' => $destinationRow['city'],
            'country' => $destinationRow['country'],
            'description' => $destinationRow['description'],
            'minPrice' => (float)$destinationRow['min_price'],
            'imageUrl' => $imageUrl,
            'label' => $destinationRow['city'],
        ];
    }

    $originCitiesStatement = $pdo->prepare(
        'SELECT oc.id, oc.city, oc.country
         FROM origin_cities oc
         INNER JOIN routes r ON r.origin_city_id = oc.id
         GROUP BY oc.id, oc.city, oc.country
         ORDER BY oc.city ASC'
    );
    $originCitiesStatement->execute();

    foreach ($originCitiesStatement->fetchAll() as $originCityRow) {
        $originCityId = (int)$originCityRow['id'];
        $originCityRow['id'] = $originCityId;
        $originCities[] = $originCityRow;
        $originCitiesById[$originCityId] = $originCityRow;
        $bookingOriginCities[(string)$originCityId] = [
            'id' => $originCityId,
            'city' => $originCityRow['city'],
            'country' => $originCityRow['country'],
            'label' => $originCityRow['city'],
        ];
    }

    $routesStatement = $pdo->prepare(
        'SELECT origin_city_id, destination_id, price
         FROM routes
         ORDER BY origin_city_id ASC, destination_id ASC'
    );
    $routesStatement->execute();

    foreach ($routesStatement->fetchAll() as $routeRow) {
        $destinationId = (int)$routeRow['destination_id'];
        $originCityId = (int)$routeRow['origin_city_id'];
        $bookingPrices[(string)$originCityId][(string)$destinationId] = (float)$routeRow['price'];
    }
} catch (PDOException $exception) {
    $loadError = 'Ndodhi një gabim. Ju lutemi provoni përsëri më vonë.';
}

$requestedDestinationId = 0;

if (isset($_GET['destination_id'])) {
    $requestedDestinationId = (int)$_GET['destination_id'];
}

if ($requestedDestinationId > 0 && isset($destinationsById[$requestedDestinationId])) {
    $old['destination_id'] = (string)$requestedDestinationId;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $value) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    $selectedDestinationId = (int)$old['destination_id'];
    $selectedOriginCityId = (int)$old['origin_city_id'];
    $bookingPagePath = $GLOBALS['base_url'] . '/pages/booking.php';

    if ($selectedDestinationId > 0) {
        $bookingPagePath .= '?destination_id=' . $selectedDestinationId;
    }

    if (!$currentUser) {
        setFlash('flash_error', 'Ju duhet të kyçeni për të bërë një rezervim.');
        redirect($GLOBALS['base_url'] . '/login.php?redirect=' . urlencode($bookingPagePath));
    }

    if ($currentUser->getRole() !== 'customer') {
        setFlash('flash_error', 'Vetëm klientët mund të bëjnë rezervime.');
        redirect($bookingPagePath);
    }

    if ($loadError !== '') {
        $bookingError = $loadError;
    } else {
        if ($selectedDestinationId < 1 || !isset($destinationsById[$selectedDestinationId])) {
            $errors['destination_id'] = 'Zgjidhni destinacionin.';
        }

        if ($selectedOriginCityId < 1 || !isset($originCitiesById[$selectedOriginCityId])) {
            $errors['origin_city_id'] = 'Zgjidhni qytetin e nisjes.';
        } elseif (!isset($bookingPrices[(string)$selectedOriginCityId][(string)$selectedDestinationId])) {
            $errors['origin_city_id'] = 'Nuk u gjet ofertë për këtë nisje.';
        }

        if ($old['depart'] === '') {
            $errors['depart'] = 'Zgjidhni datën e nisjes.';
        } elseif ($old['depart'] < $today) {
            $errors['depart'] = 'Data e nisjes nuk mund të jetë në të kaluarën.';
        }

        if ($old['return'] !== '' && $old['depart'] !== '' && $old['return'] < $old['depart']) {
            $errors['return'] = 'Data e kthimit nuk mund të jetë para nisjes.';
        }

        $errors['passengers'] = validatePassengers($old['passengers']);

        if (!array_filter($errors)) {
            try {
                $routeStatement = $pdo->prepare(
                    'SELECT id, price
                     FROM routes
                     WHERE origin_city_id = :origin_city_id AND destination_id = :destination_id
                     LIMIT 1'
                );
                $routeStatement->execute([
                    'origin_city_id' => $selectedOriginCityId,
                    'destination_id' => $selectedDestinationId,
                ]);

                $selectedRoute = $routeStatement->fetch();

                if (!$selectedRoute) {
                    $errors['origin_city_id'] = 'Nuk u gjet ofertë për këtë nisje.';
                } else {
                    $passengersCount = (int)$old['passengers'];
                    $totalPrice = (float)$selectedRoute['price'] * $passengersCount;

                    if ($old['return'] !== '') {
                        $totalPrice *= 2;
                    }

                    $insertStatement = $pdo->prepare(
                        'INSERT INTO bookings (user_id, route_id, departure_date, return_date, passengers_count, total_price, status)
                         VALUES (:user_id, :route_id, :departure_date, :return_date, :passengers_count, :total_price, :status)'
                    );
                    $insertStatement->execute([
                        'user_id' => $currentUser->getId(),
                        'route_id' => $selectedRoute['id'],
                        'departure_date' => $old['depart'],
                        'return_date' => $old['return'] !== '' ? $old['return'] : null,
                        'passengers_count' => $passengersCount,
                        'total_price' => $totalPrice,
                        'status' => 'active',
                    ]);

                    $selectedDestination = $destinationsById[$selectedDestinationId];
                    $selectedOriginCity = $originCitiesById[$selectedOriginCityId];
                    $originCityLabel = $selectedOriginCity['city'];
                    setcookie('preferred_city', $selectedDestination['city'], time() + 60 * 60 * 24 * 30, '/');

                    $_SESSION['popup_success'] =
                        'Rezervimi u ruajt me sukses për <strong>' . e($selectedDestination['city']) . '</strong>.<br><br>' .
                        'Nisja nga: <strong>' . e($originCityLabel) . '</strong><br>' .
                        'Pasagjerë: <strong>' . e((string)$passengersCount) . '</strong><br>' .
                        'Data e nisjes: <strong>' . e($old['depart']) . '</strong>' .
                        ($old['return'] !== '' ? '<br>Data e kthimit: <strong>' . e($old['return']) . '</strong>' : '') .
                        '<br>Totali: <strong>$' . e(number_format($totalPrice, 2)) . '</strong>';

                    redirect($bookingPagePath);
                }
            } catch (PDOException $exception) {
                $bookingError = 'Ndodhi një gabim. Ju lutemi provoni përsëri më vonë.';
            }
        }
    }
}

$selectedDestinationId = (int)$old['destination_id'];
$selectedOriginCityId = (int)$old['origin_city_id'];
$selectedDestination = $selectedDestinationId > 0 && isset($destinationsById[$selectedDestinationId])
    ? $destinationsById[$selectedDestinationId]
    : null;

$selectedOfferPanel = $bookingDefaultPanel;

if ($selectedDestination) {
    $selectedOfferPanel = [
        'eyebrow' => $selectedDestination['country'],
        'title' => $selectedDestination['city'],
        'subtitle' => '',
        'description' => $selectedDestination['description'],
        'imageUrl' => $GLOBALS['base_url'] . '/' . ltrim(buildDestinationImagePath($selectedDestination), '/'),
    ];
}

$offerPriceText = '';
$showOfferPrice = false;

if (
    $selectedDestination &&
    $selectedOriginCityId > 0 &&
    isset($originCitiesById[$selectedOriginCityId]) &&
    isset($bookingPrices[(string)$selectedOriginCityId][(string)$selectedDestinationId])
) {
    $passengersCount = (int)$old['passengers'];
    if ($passengersCount < 1) {
        $passengersCount = 1;
    }

    $offerTotal = (float)$bookingPrices[(string)$selectedOriginCityId][(string)$selectedDestinationId] * $passengersCount;

    if ($old['return'] !== '') {
        $offerTotal *= 2;
    }

    $selectedOriginCity = $originCitiesById[$selectedOriginCityId];
    $offerOriginLabel = $selectedOriginCity['city'];
    $offerPriceText = 'Nga ' . $offerOriginLabel . ' • $' . number_format($offerTotal, 2);
    $showOfferPrice = true;
}

$loginRedirectPath = $GLOBALS['base_url'] . '/pages/booking.php';

if ($selectedDestinationId > 0) {
    $loginRedirectPath .= '?destination_id=' . $selectedDestinationId;
}

$loginUrl = $GLOBALS['base_url'] . '/login.php?redirect=' . urlencode($loginRedirectPath);
$hasFormErrors = !empty(array_filter($errors));
$pageTitle = 'Rezervo';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
?>

<main class="page-wrap booking-page">
    <?php if ($flashSuccess): ?>
        <div class="alert success"><?= e($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert error"><?= e($flashError) ?></div>
    <?php endif; ?>

    <?php if ($loadError): ?>
        <div class="alert error"><?= e($loadError) ?></div>
    <?php else: ?>
        <section class="booking-section booking-page-layout">
            <article
                class="offer-panel<?= $selectedDestination ? ' has-destination' : ' is-default' ?>"
                id="selectedDestinationCard"
            >
                <div
                    class="offer-panel-media"
                    id="offerPanelMedia"
                    style="background-image: url('<?= e($selectedOfferPanel['imageUrl']) ?>');"
                ></div>

                <div class="offer-panel-overlay"></div>

                <div class="offer-panel-body" id="offerPanelBody">
                    <span class="offer-eyebrow" id="destinationEyebrow"><?= e($selectedOfferPanel['eyebrow']) ?></span>
                    <h2 id="destinationTitle"><?= e($selectedOfferPanel['title']) ?></h2>
                    <p
                        class="offer-subtitle<?= $selectedOfferPanel['subtitle'] === '' ? ' is-hidden' : '' ?>"
                        id="destinationSubtitle"
                    ><?= e($selectedOfferPanel['subtitle']) ?></p>
                    <p class="offer-description" id="destinationDescription"><?= e($selectedOfferPanel['description']) ?></p>

                    <div
                        class="offer-price-box<?= $showOfferPrice ? '' : ' is-hidden' ?>"
                        id="offerPriceBox"
                    >
                        <span id="offerPriceText"><?= e($offerPriceText) ?></span>
                    </div>
                </div>
            </article>

            <div class="booking-container booking-form-panel">
                <h2>Detajet e rezervimit</h2>
                <p class="section-subtitle booking-short-copy">Zgjidhni nisjen, destinacionin dhe datat.</p>

                <?php if ($bookingError): ?>
                    <div class="alert error"><?= e($bookingError) ?></div>
                <?php endif; ?>

                <form id="bookingForm" class="booking-form" method="POST" action="<?= $GLOBALS['base_url'] ?>/pages/booking.php" novalidate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="origin_city_id">Qyteti i nisjes</label>
                            <select id="origin_city_id" name="origin_city_id">
                                <option value="">Zgjidh qytetin</option>
                                <?php foreach ($originCities as $originCity): ?>
                                    <option value="<?= e((string)$originCity['id']) ?>" <?= $old['origin_city_id'] === (string)$originCity['id'] ? 'selected' : '' ?>>
                                        <?= e($originCity['city']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($errors['origin_city_id']): ?>
                                <div class="field-error"><?= e($errors['origin_city_id']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="destination_id">Destinacioni</label>
                            <select id="destination_id" name="destination_id">
                                <option value="">Zgjidh destinacionin</option>
                                <?php foreach ($destinations as $destination): ?>
                                    <option value="<?= e((string)$destination['id']) ?>" <?= $old['destination_id'] === (string)$destination['id'] ? 'selected' : '' ?>>
                                        <?= e($destination['city']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($errors['destination_id']): ?>
                                <div class="field-error"><?= e($errors['destination_id']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="depart">Data e nisjes</label>
                            <input type="date" id="depart" name="depart" value="<?= e($old['depart']) ?>" min="<?= e($today) ?>">
                            <?php if ($errors['depart']): ?>
                                <div class="field-error"><?= e($errors['depart']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="return">Data e kthimit</label>
                            <input type="date" id="return" name="return" value="<?= e($old['return']) ?>" min="<?= e($old['depart'] !== '' ? $old['depart'] : $today) ?>">
                            <?php if ($errors['return']): ?>
                                <div class="field-error"><?= e($errors['return']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="passengers">Pasagjerët</label>
                        <select id="passengers" name="passengers">
                            <?php for ($count = 1; $count <= 5; $count++): ?>
                                <option value="<?= $count ?>" <?= $old['passengers'] === (string)$count ? 'selected' : '' ?>>
                                    <?= $count ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <?php if ($errors['passengers']): ?>
                            <div class="field-error"><?= e($errors['passengers']) ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if ($currentUser && $currentUser->getRole() === 'customer'): ?>
                        <div class="booking-account-box">
                            Rezervimi ruhet te llogaria juaj.<br>
                            <strong><?= e($currentUser->getEmail()) ?></strong>
                        </div>
                    <?php elseif (!$currentUser): ?>
                        <div class="booking-account-box booking-login-box" id="guestLoginNotice">
                            <span>Kyçuni për të përfunduar rezervimin.</span>
                            <a id="bookingLoginLink" href="<?= e($loginUrl) ?>">Kyçuni</a>
                        </div>
                    <?php else: ?>
                        <div class="booking-account-box booking-admin-box">
                            Vetëm klientët mund të rezervojnë online.
                        </div>
                    <?php endif; ?>

                    <div class="booking-button-container">
                        <?php if ($currentUser && $currentUser->getRole() === 'customer'): ?>
                            <button type="submit" class="btn-primary btn-full">Rezervo tani</button>
                        <?php elseif (!$currentUser): ?>
                            <button id="guestBookingButton" type="button" class="btn-primary btn-full">Rezervo tani</button>
                        <?php else: ?>
                            <a href="<?= $GLOBALS['base_url'] ?>/pages/admin-dashboard.php" class="btn-primary btn-full">Kthehu te dashboard</a>
                        <?php endif; ?>

                        <div id="livePrice">Çmimi: $0</div>
                    </div>
                </form>

            </div>
        </section>

        <section class="booking-extra-card weather-api-card" id="destinationWeatherCard">
            <div class="dashboard-section-header">
                <div>
                    <h3>Moti aktual</h3>
                    <p class="dashboard-section-subtitle">Të dhëna për destinacionin e zgjedhur.</p>
                </div>
            </div>

            <div id="weatherApiLoading" class="table-action-note" hidden>Duke ngarkuar motin...</div>
            <p id="weatherApiEmpty" class="table-empty">Zgjidhni një destinacion për të parë motin aktual.</p>
            <p id="weatherApiError" class="alert error" hidden>Nuk u ngarkua moti për këtë destinacion.</p>

            <div id="weatherApiContent" class="weather-meta-list" hidden>
                <div>
                    <span class="dashboard-meta-label">Qyteti</span>
                    <strong id="weatherApiLocation">-</strong>
                </div>
                <div>
                    <span class="dashboard-meta-label">Temperatura</span>
                    <strong id="weatherApiTemperature">-</strong>
                </div>
                <div>
                    <span class="dashboard-meta-label">Kushtet</span>
                    <strong id="weatherApiCondition">-</strong>
                </div>
                <div>
                    <span class="dashboard-meta-label">Era</span>
                    <strong id="weatherApiWind">-</strong>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<div id="successPopup" class="popup-overlay" style="<?= $popupSuccess ? 'display: flex;' : 'display: none;' ?>">
    <div class="popup-box">
        <h2>Rezervimi u krye me sukses!</h2>
        <p id="popupMessage"><?= $popupSuccess ?></p>
        <div class="popup-actions">
            <button id="closePopup" class="btn-secondary" type="button">Mbylle</button>
            <a href="<?= $GLOBALS['base_url'] ?>/pages/customer-dashboard.php#reservations" class="btn-primary">Shiko rezervimet</a>
        </div>
    </div>
</div>

<script>
window.bookingDefaultPanel = <?= json_encode($bookingDefaultPanel, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
window.bookingOriginCities = <?= json_encode($bookingOriginCities, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', function () {
    const hasFormErrors = <?= $hasFormErrors ? 'true' : 'false' ?>;

    if (hasFormErrors) {
        const bookingForm = document.getElementById('bookingForm');
        if (bookingForm) {
            bookingForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
