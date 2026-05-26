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
    $recentMessagesStatement->execute();
    $recentMessages = $recentMessagesStatement->fetchAll();
} catch (PDOException $exception) {
    $dashboardError = 'Ndodhi nje gabim. Ju lutemi provoni perseri me vone.';
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
?>
<main class="page-wrap dashboard-page">
    <section class="dashboard-hero">
        <div>
            <h1>Admin Dashboard</h1>
            <p class="page-subtitle"><?= e($user->getDashboardMessage()) ?></p>
        </div>
    </section>

    <?php if ($flashSuccess): ?>
        <div class="alert success"><?= e($flashSuccess) ?></div>
    <?php endif; ?>
<?php if ($dashboardError): ?>
        <div class="alert error"><?= e($dashboardError) ?></div>
    <?php else: ?>
        <section class="dashboard-stats">
            <article class="dashboard-stat-card">
                <span class="dashboard-stat-label">Rezervime</span>
                <strong class="dashboard-stat-value"><?= e((string)$overview['total_bookings']) ?></strong>
            </article>
            <article class="dashboard-stat-card">
                <span class="dashboard-stat-label">Mesazhe</span>
                <strong class="dashboard-stat-value"><?= e((string)$overview['total_messages']) ?></strong>
            </article>
        </section>

        <section class="dashboard-card">
            <div class="dashboard-section-header">
                <div>
                    <h3>Llogaria juaj</h3>
                    <p class="dashboard-section-subtitle">Te dhenat baze te administratorit te kycur.</p>
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
            </div>
        </section>
<section class="dashboard-card">
            <div class="dashboard-section-header">
                <div>
                    <h3>Menaxhimi i ofertave</h3>
                    <p class="dashboard-section-subtitle">Shtoni dhe perditesoni destinacionet, nisjet dhe rruget e fluturimeve.</p>
                </div>
            </div>

            <div class="admin-links-grid">
                <a class="admin-link-card" href="<?= $GLOBALS['base_url'] ?>/pages/admin-destinations.php">
                    <strong>Menaxho destinacionet</strong>
                    <span>Qytetet, shtetet, pershkrimet dhe imazhet.</span>
                </a>
                <a class="admin-link-card" href="<?= $GLOBALS['base_url'] ?>/pages/admin-origin-cities.php">
                    <strong>Menaxho nisjet</strong>
                    <span>Qytetet e nisjes dhe shtetet perkatese.</span>
                </a>

                <a class="admin-link-card" href="<?= $GLOBALS['base_url'] ?>/pages/admin-routes.php">
                    <strong>Menaxho rruget</strong>
                    <span>Nisjet, destinacionet dhe cmimet perkatese.</span>
                </a>
            </div>
        </section>
<section class="dashboard-card">
            <div class="dashboard-section-header">
                <div>
                    <h3>Rezervimet e fundit</h3>
                    <p class="dashboard-section-subtitle">Pese rezervimet me te reja te ruajtura ne sistem.</p>
                </div>
            </div>

            <?php if (!$recentBookings): ?>
                <p class="table-empty">Nuk ka ende rezervime te ruajtura.</p>
            <?php else: ?>
                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Klienti</th>
                            <th>Rruga</th>
                            <th>Pasagjere</th>
                            <th>Totali</th>
                            <th>Statusi</th>
                            <th>Ruajtur me</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $booking): ?>
                            <?php $isCancelled = ($booking['status'] ?? 'active') === 'cancelled'; ?>
                            <tr>
                                <td><?= e((string)$booking['id']) ?></td>
                                <td><?= e($booking['full_name']) ?></td>
                                <td><?= e($booking['origin_city'] . ' / ' . $booking['destination_city']) ?></td>
                                <td><?= e((string)$booking['passengers_count']) ?></td>
                                <td>$<?= e(number_format((float)$booking['total_price'], 2)) ?></td>
                                 <td>
                                    <span class="status-badge <?= $isCancelled ? 'status-badge--cancelled' : 'status-badge--active' ?>">
                                        <?= e($statusLabel($booking['status'] ?? 'active')) ?>
                                    </span>
                                </td>
                                <td><?= e($formatDateTime($booking['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="dashboard-card" id="messages">
            <div class="dashboard-section-header">
                <div>
                    <h3>Mesazhet e fundit</h3>
                    <p class="dashboard-section-subtitle">Pese mesazhet me te reja nga forma e kontaktit.</p>
                </div>
            </div>
