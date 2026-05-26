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
try {
    $pdo = getPDO();
    $bookingsStatement = $pdo->prepare(
        'SELECT
            b.id,
            b.status,
            oc.city AS origin_city,
            oc.country AS origin_country,
            d.city AS destination_city,
            d.country AS destination_country,
            b.departure_date,
            b.return_date,
            b.passengers_count,
            b.total_price,
            b.created_at
         FROM bookings b
         INNER JOIN routes r ON r.id = b.route_id
         INNER JOIN origin_cities oc ON oc.id = r.origin_city_id
         INNER JOIN destinations d ON d.id = r.destination_id
         WHERE b.user_id = :user_id
         ORDER BY b.created_at DESC, b.id DESC'
    );
     $bookingsStatement->execute(['user_id' => $user->getId()]);
    $customerBookings = $bookingsStatement->fetchAll();
} catch (PDOException $exception) {
    $bookingError = 'Ndodhi nje gabim. Ju lutemi provoni perseri me vone.';
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
?>

<main class="page-wrap dashboard-page">
    <section class="dashboard-hero">
        <div>
            <h1>Dashboard</h1>
            <p class="page-subtitle"><?= e($user->getDashboardMessage()) ?></p>
        </div>
    </section>

    <?php if ($flashSuccess): ?>
        <div class="alert success"><?= e($flashSuccess) ?></div>
    <?php endif; ?>
<section class="dashboard-card">
        <div class="dashboard-section-header">
            <div>
                <h3>Llogaria juaj</h3>
                <p class="dashboard-section-subtitle">Te dhenat baze te llogarise suaj dhe hyrja e fundit.</p>
            </div>
        </div>

        <div class="dashboard-meta-list">
            <div>
                <span class="dashboard-meta-label">Perdoruesi</span>
                <strong><?= e($user->getName()) ?></strong>
            </div>
            <div>
                <span class="dashboard-meta-label">Email</span>
                <strong><?= e($user->getEmail()) ?></strong>
            </div>
            <div>
                <span class="dashboard-meta-label">Roli</span>
                <strong><?= e($user->getRole()) ?></strong>
            </div>
            <div>
                <span class="dashboard-meta-label">Hyrja e fundit</span>
                <strong><?= e($formatDateTime($_SESSION['last_login'] ?? null)) ?></strong>
            </div>
        </div>
    </section>

    <section class="dashboard-card" id="reservations">
        <div class="dashboard-section-header">
            <div>
                <h3>Rezervimet e mia</h3>
                <p class="dashboard-section-subtitle">Te gjitha rezervimet e ruajtura ne llogarine tuaj.</p>
            </div>
        </div>
