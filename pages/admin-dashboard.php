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
try {
    $pdo = getPDO();

    $overviewStatement = $pdo->prepare(
        "SELECT
            (SELECT COUNT(*) FROM bookings) AS total_bookings,
            (SELECT COUNT(*) FROM contact_messages) AS total_messages"
    );
    $overviewStatement->execute();
    $overviewData = $overviewStatement->fetch();

    if ($overviewData) {
        $overview = [
            'total_bookings' => (int)$overviewData['total_bookings'],
            'total_messages' => (int)$overviewData['total_messages'],
        ];
    }