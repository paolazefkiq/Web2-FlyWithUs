<?php

require_once __DIR__ . '/../includes/config.php';
requireRole('admin');

$user = currentUser();
$pageTitle = 'Admin Dashboard';
$flashSuccess = getFlash('flash_success');
$dashboardError = '';
$overview = [
    'total_bookings' => 0,
    'total_messages' => 0,
];
$recentBookings = [];
$recentMessages = [];

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
