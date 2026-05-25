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