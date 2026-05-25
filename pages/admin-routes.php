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