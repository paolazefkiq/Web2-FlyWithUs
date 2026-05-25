<?php

require_once __DIR__ . '/../includes/config.php';
requireRole('admin');

$pageTitle = 'Menaxho Destinacionet';
$flashSuccess = getFlash('flash_success');
$flashError = getFlash('flash_error');
$formError = '';
$destinations = [];
$editingId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$uploadRelativeDirectory = 'assets/img/destinations';
$uploadDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . 
DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'destinations';
$formData = [
    'city' => '',
    'country' => '',
    'description' => '',
    'image_path' => '',
];
$errors = [
    'city' => '',
    'country' => '',
    'description' => '',
    'image' => '',
];

$validateDestinationForm = static function (array $data): array {
    $errors = [
        'city' => '',
        'country' => '',
        'description' => '',
        'image' => '',
    ];

    if ($data['country'] === '') {
        $errors['country'] = 'Plotësoni shtetin.';
    } elseif (!preg_match('/^[A-Za-zÇçËë\s\-]{2,80}$/', $data['country'])) {
        $errors['country'] = 'Shteti duhet te permbaje vetem shkronja.';
    }

    if ($data['description'] === '') {
        $errors['description'] = 'Plotësoni përshkrimin.';
    } elseif (mb_strlen($data['description']) < 10) {
        $errors['description'] = 'Përshkrimi duhet të ketë së paku 10 karaktere.';
    }

    return $errors;
};
