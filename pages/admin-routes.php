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