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

