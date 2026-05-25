<?php

require_once __DIR__ . '/../includes/config.php';
requireRole('admin');

$pageTitle = 'Menaxho Rruget';
$flashSuccess = getFlash('flash_success');
$flashError = getFlash('flash_error');
$formError = '';
$routes = [];
$originCities = [];
$destinations = [];
$availableOriginCities = [];
$availableDestinationsByOrigin = [];
$editingId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$prefillDestinationId = isset($_GET['destination_id']) ? (int)$_GET['destination_id'] : 0;
$formData = [
    'origin_city_id' => '',
    'destination_id' => '',
    'price' => '',
];
$errors = [
    'origin_city_id' => '',
    'destination_id' => '',
    'price' => '',
];

$validateRouteForm = static function (array $data): array {
    $errors = [
        'origin_city_id' => '',
        'destination_id' => '',
        'price' => '',
    ];

    if ((int)$data['origin_city_id'] < 1) {
        $errors['origin_city_id'] = 'Zgjidhni qytetin e nisjes.';
    }

    if ((int)$data['destination_id'] < 1) {
        $errors['destination_id'] = 'Zgjidhni destinacionin.';
    }

    if ($data['price'] === '') {
        $errors['price'] = 'Plotesoni cmimin.';
    } elseif (!is_numeric($data['price']) || (float)$data['price'] <= 0) {
        $errors['price'] = 'Cmimi duhet te jete numer pozitiv.';
    }

    return $errors;
};
try {
    $pdo = getPDO();

    $originCitiesStatement = $pdo->prepare('SELECT id, city FROM origin_cities ORDER BY city ASC');
    $originCitiesStatement->execute();
    $originCities = $originCitiesStatement->fetchAll();

    $destinationsStatement = $pdo->prepare('SELECT id, city FROM destinations ORDER BY city ASC');
    $destinationsStatement->execute();
    $destinations = $destinationsStatement->fetchAll();

    $existingRoutesStatement = $pdo->prepare(
        'SELECT origin_city_id, destination_id
         FROM routes
         ORDER BY origin_city_id ASC, destination_id ASC'
    );
    $existingRoutesStatement->execute();
    $existingRoutes = $existingRoutesStatement->fetchAll();

    $existingDestinationIdsByOrigin = [];
    $destinationLabelsById = [];

    foreach ($destinations as $destination) {
        $destinationLabelsById[(int)$destination['id']] = $destination['city'];
    }

    foreach ($existingRoutes as $existingRoute) {
        $originId = (int)$existingRoute['origin_city_id'];
        $destinationId = (int)$existingRoute['destination_id'];
        $existingDestinationIdsByOrigin[$originId][$destinationId] = true;
    }

    foreach ($originCities as $originCity) {
        $originId = (int)$originCity['id'];
        $missingDestinations = [];

        foreach ($destinations as $destination) {
            $destinationId = (int)$destination['id'];

            if (!isset($existingDestinationIdsByOrigin[$originId][$destinationId])) {
                $missingDestinations[] = [
                    'id' => $destinationId,
                    'city' => $destination['city'],
                ];
            }
        }

        if ($missingDestinations) {
            $availableOriginCities[] = $originCity;
            $availableDestinationsByOrigin[(string)$originId] = $missingDestinations;
        }
    }

    $destinationIds = array_map(static fn(array $destination): int => (int)$destination['id'], $destinations);

    if ($prefillDestinationId > 0 && !$editingId && $_SERVER['REQUEST_METHOD'] !== 'POST' && in_array($prefillDestinationId, $destinationIds, true)) {
        $formData['destination_id'] = (string)$prefillDestinationId;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'delete') {
            $routeId = (int)($_POST['route_id'] ?? 0);

            if ($routeId > 0) {
                $bookingCountStatement = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE route_id = :route_id');
                $bookingCountStatement->execute(['route_id' => $routeId]);
                $bookingCount = (int)$bookingCountStatement->fetchColumn();

                if ($bookingCount > 0) {
                    setFlash('flash_error', 'Kjo rruge ka rezervime te lidhura dhe nuk mund te fshihet.');
                } else {
                    $deleteStatement = $pdo->prepare('DELETE FROM routes WHERE id = :id');
                    $deleteStatement->execute(['id' => $routeId]);
                    setFlash('flash_success', 'Rruga u fshi me sukses.');
                }
            }

            redirect($GLOBALS['base_url'] . '/pages/admin-routes.php');
        }

        $formData = [
            'origin_city_id' => trim($_POST['origin_city_id'] ?? ''),
            'destination_id' => trim($_POST['destination_id'] ?? ''),
            'price' => trim($_POST['price'] ?? ''),
        ];
        $errors = $validateRouteForm($formData);

        if (!array_filter($errors)) {
            $selectedOriginCityId = (int)$formData['origin_city_id'];
            $selectedDestinationId = (int)$formData['destination_id'];
            $currentRouteId = $action === 'update' ? (int)($_POST['route_id'] ?? 0) : 0;

            $existingRouteSql = '
                SELECT id, price
                FROM routes
                WHERE origin_city_id = :origin_city_id
                  AND destination_id = :destination_id';
            $existingRouteParams = [
                'origin_city_id' => $selectedOriginCityId,
                'destination_id' => $selectedDestinationId,
            ];

            if ($currentRouteId > 0) {
                $existingRouteSql .= ' AND id <> :current_route_id';
                $existingRouteParams['current_route_id'] = $currentRouteId;
            }

            $existingRouteSql .= ' LIMIT 1';

            $existingRouteStatement = $pdo->prepare($existingRouteSql);
            $existingRouteStatement->execute($existingRouteParams);
            $existingRoute = $existingRouteStatement->fetch();
        
         if ($existingRoute) {
                $currentPrice = '$' . number_format((float)$existingRoute['price'], 2);
                $formError = 'Kjo rruge ekziston tashme. Cmimi aktual: ' . $currentPrice . '. Per perditsim, perdorni butonin "Edito" ne listen me poshte.';
            } else {
                try {
                    if ($action === 'update') {
                        $routeId = (int)($_POST['route_id'] ?? 0);
                        $statement = $pdo->prepare(
                            'UPDATE routes
                             SET origin_city_id = :origin_city_id,
                                 destination_id = :destination_id,
                                 price = :price
                             WHERE id = :id'
                        );
                        $statement->execute([
                            'origin_city_id' => $selectedOriginCityId,
                            'destination_id' => $selectedDestinationId,
                            'price' => $formData['price'],
                            'id' => $routeId,
                        ]);
                        setFlash('flash_success', 'Rruga u perditesua me sukses.');
                    } else {
                        $statement = $pdo->prepare(
                            'INSERT INTO routes (origin_city_id, destination_id, price)
                             VALUES (:origin_city_id, :destination_id, :price)'
                        );
                        $statement->execute([
                            'origin_city_id' => $selectedOriginCityId,
                            'destination_id' => $selectedDestinationId,
                            'price' => $formData['price'],
                        ]);
                        setFlash('flash_success', 'Rruga u shtua me sukses.');
                    }

                    redirect($GLOBALS['base_url'] . '/pages/admin-routes.php');
                } catch (PDOException $exception) {
                    $formError = 'Rruga nuk u ruajt. Ju lutemi kontrolloni te dhenat dhe provoni perseri.';
                }
            }
        }
    }
     if ($editingId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $editStatement = $pdo->prepare(
            'SELECT id, origin_city_id, destination_id, price
             FROM routes
             WHERE id = :id
             LIMIT 1'
        );
        $editStatement->execute(['id' => $editingId]);
        $editingRoute = $editStatement->fetch();

        if ($editingRoute) {
            $formData = [
                'origin_city_id' => (string)$editingRoute['origin_city_id'],
                'destination_id' => (string)$editingRoute['destination_id'],
                'price' => (string)$editingRoute['price'],
            ];
        } else {
            setFlash('flash_error', 'Rruga nuk u gjet.');
            redirect($GLOBALS['base_url'] . '/pages/admin-routes.php');
        }
    }

    $routesStatement = $pdo->prepare(
        'SELECT
            r.id,
            oc.city AS origin_city,
            d.city AS destination_city,
            r.price,
            COUNT(b.id) AS bookings_count
         FROM routes r
         INNER JOIN origin_cities oc ON oc.id = r.origin_city_id
         INNER JOIN destinations d ON d.id = r.destination_id
         LEFT JOIN bookings b ON b.route_id = r.id
         GROUP BY r.id, oc.city, d.city, r.price
         ORDER BY oc.city ASC, d.city ASC'
    );
    $routesStatement->execute();
    $routes = $routesStatement->fetchAll();
} catch (PDOException $exception) {
    $formError = 'Ndodhi nje gabim. Ju lutemi provoni perseri me vone.';
}