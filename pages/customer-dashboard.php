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
 <div id="bookingActionMessage" class="alert info" hidden></div>

        <?php if ($bookingError): ?>
            <div class="alert error"><?= e($bookingError) ?></div>
        <?php elseif (!$customerBookings): ?>
            <p class="table-empty">Nuk keni ende rezervime te ruajtura.</p>
        <?php else: ?>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rruga</th>
                        <th>Nisja</th>
                        <th>Kthimi</th>
                        <th>Pasagjere</th>
                        <th>Totali</th>
                        <th>Statusi</th>
                        <th>Veprime</th>
                        <th>Ruajtur me</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customerBookings as $booking): ?>
                        <?php $isCancelled = ($booking['status'] ?? 'active') === 'cancelled'; ?>
                        <tr id="booking-row-<?= e((string)$booking['id']) ?>">
                            <td><?= e((string)$booking['id']) ?></td>
                            <td><?= e($booking['origin_city'] . ' - ' . $booking['origin_country'] . ' / ' . $booking['destination_city'] . ' - ' . $booking['destination_country']) ?></td>
                            <td><?= e($booking['departure_date']) ?></td>
                            <td><?= e($booking['return_date'] ?? '-') ?></td>
                            <td><?= e((string)$booking['passengers_count']) ?></td>
                            <td>$<?= e(number_format((float)$booking['total_price'], 2)) ?></td>
                            <td>
                                <span
                                    class="status-badge <?= $isCancelled ? 'status-badge--cancelled' : 'status-badge--active' ?>"
                                    data-booking-status
                                >
                                    <?= e($statusLabel($booking['status'] ?? 'active')) ?>
                                </span>
                            </td>
                            <td data-booking-actions>
                                <?php if ($isCancelled): ?>
                                    <span class="table-action-note">Anuluar</span>
                                <?php else: ?>
                                    <button
                                        type="button"
                                        class="btn-danger btn-small js-cancel-booking"
                                        data-booking-id="<?= e((string)$booking['id']) ?>"
                                    >
                                        Anulo rezervimin
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td><?= e($formatDateTime($booking['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>