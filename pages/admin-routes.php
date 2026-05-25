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
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
?>

<main class="page-wrap dashboard-page">
    <section class="dashboard-hero">
        <div>
            <h1>Menaxho Rruget</h1>
            <p class="page-subtitle">Lidh qytetet e nisjes me destinacionet dhe cmimet perkatese.</p>
        </div>
        <div class="admin-toolbar">
            <a href="<?= $GLOBALS['base_url'] ?>/pages/admin-dashboard.php" class="btn-secondary">Kthehu te dashboard</a>
            <a href="<?= $GLOBALS['base_url'] ?>/pages/admin-origin-cities.php" class="btn-secondary">Menaxho nisjet</a>
            <a href="<?= $GLOBALS['base_url'] ?>/pages/admin-destinations.php" class="btn-primary">Menaxho destinacionet</a>
        </div>
    </section>

    <?php if ($flashSuccess): ?>
        <div class="alert success"><?= e($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert error"><?= e($flashError) ?></div>
    <?php endif; ?>

    <?php if ($formError): ?>
        <div class="alert error"><?= e($formError) ?></div>
    <?php endif; ?>

    <section class="dashboard-card">
        <div class="dashboard-section-header">
            <div>
                <h3><?= $editingId > 0 ? 'Perditeso rrugen' : 'Shto rruge te re' ?></h3>
                <p class="dashboard-section-subtitle">
                    <?php if ($prefillDestinationId > 0 && !$editingId): ?>
                        Destinacioni u zgjodh nga lista e destinacioneve. Zgjidhni nisjen dhe cmimin.
                    <?php else: ?>
                        Cdo kombinim i nisjes dhe destinacionit ruhet nje here.
                    <?php endif; ?>
                </p>
            </div>
        </div>
     <?php if (!$editingId && !$availableOriginCities): ?>
            <div class="alert info">
                Te gjitha rruget e mundshme jane tashme te ruajtura. Per ndryshim cmimi, perdorni butonin "Edito" ne listen me poshte ose shtoni nje destinacion apo qytet te ri nisjeje.
            </div>
        <?php endif; ?>

        <form method="POST" class="crud-form" novalidate>
            <input type="hidden" name="action" value="<?= $editingId > 0 ? 'update' : 'create' ?>">
            <?php if ($editingId > 0): ?>
                <input type="hidden" name="route_id" value="<?= e((string)$editingId) ?>">
            <?php endif; ?>

            <div class="crud-grid crud-grid--three">
                <div class="form-group">
                    <label for="route-origin">Qyteti i nisjes</label>
                    <select id="route-origin" name="origin_city_id">
                        <option value="">Zgjidh qytetin</option>
                        <?php foreach ($editingId > 0 ? $originCities : $availableOriginCities as $originCity): ?>
                            <option value="<?= e((string)$originCity['id']) ?>" <?= $formData['origin_city_id'] === (string)$originCity['id'] ? 'selected' : '' ?>>
                                <?= e($originCity['city']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($errors['origin_city_id']): ?><div class="field-error"><?= e($errors['origin_city_id']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="route-destination">Destinacioni</label>
                    <select id="route-destination" name="destination_id">
                        <option value="">Zgjidh destinacionin</option>
                        <?php
                        $destinationOptions = $destinations;

                        if ($editingId === 0 && $formData['origin_city_id'] !== '' && isset($availableDestinationsByOrigin[$formData['origin_city_id']])) {
                            $destinationOptions = $availableDestinationsByOrigin[$formData['origin_city_id']];
                        } elseif ($editingId === 0 && $prefillDestinationId > 0 && isset($destinationLabelsById[$prefillDestinationId])) {
                            $destinationOptions = [
                                [
                                    'id' => $prefillDestinationId,
                                    'city' => $destinationLabelsById[$prefillDestinationId],
                                ],
                            ];
                        } elseif ($editingId === 0) {
                            $destinationOptions = [];
                        }
                        ?>
                        <?php foreach ($destinationOptions as $destination): ?>
                            <option value="<?= e((string)$destination['id']) ?>" <?= $formData['destination_id'] === (string)$destination['id'] ? 'selected' : '' ?>>
                                <?= e($destination['city']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!$editingId): ?>
                        <p class="form-help">Pas zgjedhjes se nisjes, shfaqen vetem destinacionet qe ende nuk kane rruge te ruajtur.</p>
                    <?php endif; ?>
                    <?php if ($errors['destination_id']): ?><div class="field-error"><?= e($errors['destination_id']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="route-price">Cmimi</label>
                    <input id="route-price" type="number" step="0.01" min="0" name="price" value="<?= e($formData['price']) ?>">
                    <?php if ($errors['price']): ?><div class="field-error"><?= e($errors['price']) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="crud-actions">
                <button type="submit" class="btn-primary"><?= $editingId > 0 ? 'Ruaj ndryshimet' : 'Shto rrugen' ?></button>
                <?php if ($editingId > 0): ?>
                    <a href="<?= $GLOBALS['base_url'] ?>/pages/admin-routes.php" class="btn-secondary">Anulo</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="dashboard-card">
        <div class="dashboard-section-header">
            <div>
                <h3>Lista e rrugeve</h3>
                <p class="dashboard-section-subtitle">Rruget e ruajtura me cmimet dhe perdorimin e tyre.</p>
            </div>
        </div>

        <?php if (!$routes): ?>
            <p class="table-empty">Nuk ka ende rruge te ruajtura.</p>
        <?php else: ?>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nisja</th>
                        <th>Destinacioni</th>
                        <th>Cmimi</th>
                        <th>Rezervime</th>
                        <th>Veprime</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($routes as $route): ?>
                        <tr>
                            <td><?= e((string)$route['id']) ?></td>
                            <td><?= e($route['origin_city']) ?></td>
                            <td><?= e($route['destination_city']) ?></td>
                            <td>$<?= e(number_format((float)$route['price'], 2)) ?></td>
                            <td><?= e((string)$route['bookings_count']) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= $GLOBALS['base_url'] ?>/pages/admin-routes.php?edit=<?= e((string)$route['id']) ?>" class="btn-secondary btn-small">Edito</a>
                                    <form method="POST" onsubmit="return confirm('A jeni i sigurt qe deshironi ta fshini kete rruge?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="route_id" value="<?= e((string)$route['id']) ?>">
                                        <button type="submit" class="btn-danger btn-small">Fshi</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
<?php if ($editingId === 0): ?>
    <script>
        window.availableDestinationsByOrigin = <?= json_encode($availableDestinationsByOrigin, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

        document.addEventListener('DOMContentLoaded', function () {
            const originSelect = document.getElementById('route-origin');
            const destinationSelect = document.getElementById('route-destination');
            const availableDestinations = window.availableDestinationsByOrigin || {};
            const selectedDestinationId = <?= json_encode($formData['destination_id'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

            if (!originSelect || !destinationSelect) {
                return;
            }

            const renderDestinationOptions = function () {
                const originId = originSelect.value;
                const destinations = availableDestinations[originId] || [];
                const currentValue = destinationSelect.value;

                destinationSelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Zgjidh destinacionin';
                destinationSelect.appendChild(placeholder);

                destinations.forEach(function (destination) {
                    const option = document.createElement('option');
                    option.value = String(destination.id);
                    option.textContent = destination.city;
                    destinationSelect.appendChild(option);
                });

                if (currentValue && destinations.some(function (destination) { return String(destination.id) === currentValue; })) {
                    destinationSelect.value = currentValue;
                } else if (
                    selectedDestinationId &&
                    destinations.some(function (destination) { return String(destination.id) === String(selectedDestinationId); })
                ) {
                    destinationSelect.value = String(selectedDestinationId);
                }
            };

            originSelect.addEventListener('change', renderDestinationOptions);
            renderDestinationOptions();
        });
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>