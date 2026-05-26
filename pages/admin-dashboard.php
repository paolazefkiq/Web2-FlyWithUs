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
    $recentBookingsStatement = $pdo->prepare(
        'SELECT
            b.id,
            b.status,
            u.full_name,
            oc.city AS origin_city,
            d.city AS destination_city,
            b.passengers_count,
            b.total_price,
            b.created_at
         FROM bookings b
         INNER JOIN users u ON u.id = b.user_id
         INNER JOIN routes r ON r.id = b.route_id
         INNER JOIN origin_cities oc ON oc.id = r.origin_city_id
         INNER JOIN destinations d ON d.id = r.destination_id
         ORDER BY b.created_at DESC, b.id DESC
         LIMIT 5'
    );
    $recentBookingsStatement->execute();
    $recentBookings = $recentBookingsStatement->fetchAll();

    $recentMessagesStatement = $pdo->prepare(
        'SELECT
            cm.id,
            cm.name,
            cm.email,
            cm.subject,
            cm.message,
            cm.created_at
         FROM contact_messages cm
         ORDER BY cm.created_at DESC, cm.id DESC
         LIMIT 5'
    );