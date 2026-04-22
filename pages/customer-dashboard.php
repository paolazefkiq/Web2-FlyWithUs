<?php
require_once __DIR__ . '/../includes/config.php';
requireRole('customer');
$user = currentUser();
$pageTitle = 'Customer Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
$lastBooking = $_SESSION['last_booking'] ?? null;
$flashSuccess = getFlash('flash_success');
?>
