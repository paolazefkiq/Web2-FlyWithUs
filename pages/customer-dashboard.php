<?php

require_once __DIR__ . '/../includes/config.php';
requireRole('customer');

$user = currentUser();
$pageTitle = 'Dashboard';
$flashSuccess = getFlash('flash_success');
$bookingError = '';
$customerBookings = [];

$formatDateTime = static function (?string $value): string {
    if (!$value) {
        return '-';
    }
$timestamp = strtotime($value);

    if ($timestamp === false) {
        return $value;
    }

    return date('d.m.Y H:i', $timestamp);
};

$statusLabel = static function (string $status): string {
    return $status === 'cancelled' ? 'Anuluar' : 'Aktive';
};
