<?php

require_once __DIR__ . '/../includes/config.php';
requireRole('admin');

$pageTitle = 'Menaxho Nisjet';
$flashSuccess = getFlash('flash_success');
$flashError = getFlash('flash_error');
$formError = '';
$originCities = [];
$editingId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$formData = [
    'city' => '',
    'country' => '',
];
$errors = [
    'city' => '',
    'country' => '',
];

$validateOriginCityForm = static function (array $data): array {
    $errors = [
        'city' => '',
        'country' => '',
    ];

    if ($data['city'] === '') {
    $errors['city'] = 'Plotesoni qytetin.';
} elseif (!preg_match('/^[A-Za-zÇçËë\s\-]{2,80}$/', $data['city'])) {
    $errors['city'] = 'Qyteti duhet te permbaje vetem shkronja.';
}

    if ($data['country'] === '') {
    $errors['country'] = 'Plotesoni shtetin.';
} elseif (!preg_match('/^[A-Za-zÇçËë\s\-]{2,80}$/', $data['country'])) {
    $errors['country'] = 'Shteti duhet te permbaje vetem shkronja.';
}

    return $errors;
};

try {
    $pdo = getPDO();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'delete') {
            $originCityId = (int)($_POST['origin_city_id'] ?? 0);

            if ($originCityId > 0) {
                $bookingCountStatement = $pdo->prepare(
                    'SELECT COUNT(*)
                     FROM bookings b
                     INNER JOIN routes r ON r.id = b.route_id
                     WHERE r.origin_city_id = :origin_city_id'
                );
                $bookingCountStatement->execute(['origin_city_id' => $originCityId]);
                $bookingCount = (int)$bookingCountStatement->fetchColumn();

                if ($bookingCount > 0) {
                    setFlash('flash_error', 'Ky qytet nisjeje ka rezervime te lidhura dhe nuk mund te fshihet.');
                } else {
                    $deleteStatement = $pdo->prepare('DELETE FROM origin_cities WHERE id = :id');
                    $deleteStatement->execute(['id' => $originCityId]);
                    setFlash('flash_success', 'Qyteti i nisjes dhe rruget e lidhura u fshine me sukses.');
                }
            }

            redirect($GLOBALS['base_url'] . '/pages/admin-origin-cities.php');
        }

        $formData = [
            'city' => trim($_POST['city'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
        ];
        $errors = $validateOriginCityForm($formData);

        if ($errors['city'] === '' && $formData['city'] !== '') {
            $originCityId = $action === 'update' ? (int)($_POST['origin_city_id'] ?? 0) : 0;
            $duplicateOriginStatement = $pdo->prepare(
                'SELECT id
                 FROM origin_cities
                 WHERE city = :city' . ($action === 'update' ? ' AND id <> :id' : '') . '
                 LIMIT 1'
            );
            $duplicateOriginParams = ['city' => $formData['city']];

            if ($action === 'update') {
                $duplicateOriginParams['id'] = $originCityId;
            }

            $duplicateOriginStatement->execute($duplicateOriginParams);

            if ($duplicateOriginStatement->fetch()) {
                $errors['city'] = 'Ky qytet ekziston tashme si nisje.';
            }
        }

        if (!array_filter($errors)) {
            try {
                if ($action === 'update') {
                    $originCityId = (int)($_POST['origin_city_id'] ?? 0);
                    $statement = $pdo->prepare(
                        'UPDATE origin_cities
                         SET city = :city,
                             country = :country
                         WHERE id = :id'
                    );
                    $statement->execute([
                        'city' => $formData['city'],
                        'country' => $formData['country'],
                        'id' => $originCityId,
                    ]);
                    setFlash('flash_success', 'Qyteti i nisjes u perditesua me sukses.');
                } else {
                    $statement = $pdo->prepare(
                        'INSERT INTO origin_cities (city, country)
                         VALUES (:city, :country)'
                    );
                    $statement->execute([
                        'city' => $formData['city'],
                        'country' => $formData['country'],
                    ]);
                    setFlash('flash_success', 'Qyteti i nisjes u shtua me sukses. Tani shtoni rruget te "Menaxho rruget" qe kjo nisje te perdoret ne oferta dhe rezervim.');
                }

                redirect($GLOBALS['base_url'] . '/pages/admin-origin-cities.php');
            } catch (PDOException $exception) {
                $formError = 'Qyteti i nisjes nuk u ruajt. Ju lutemi kontrolloni te dhenat dhe provoni perseri.';
            }
        }
    }

    if ($editingId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $editStatement = $pdo->prepare(
            'SELECT id, city, country
             FROM origin_cities
             WHERE id = :id
             LIMIT 1'
        );
        $editStatement->execute(['id' => $editingId]);
        $editingOriginCity = $editStatement->fetch();

        if ($editingOriginCity) {
            $formData = [
                'city' => $editingOriginCity['city'],
                'country' => $editingOriginCity['country'],
            ];
        } else {
            setFlash('flash_error', 'Qyteti i nisjes nuk u gjet.');
            redirect($GLOBALS['base_url'] . '/pages/admin-origin-cities.php');
        }
    }
    $originCitiesStatement = $pdo->prepare(
        'SELECT
            oc.id,
            oc.city,
            oc.country,
            COUNT(r.id) AS routes_count
         FROM origin_cities oc
         LEFT JOIN routes r ON r.origin_city_id = oc.id
         GROUP BY oc.id, oc.city, oc.country
         ORDER BY oc.city ASC'
    );
    $originCitiesStatement->execute();
    $originCities = $originCitiesStatement->fetchAll();
} catch (PDOException $exception) {
    $formError = 'Ndodhi nje gabim. Ju lutemi provoni perseri me vone.';
}



    
    