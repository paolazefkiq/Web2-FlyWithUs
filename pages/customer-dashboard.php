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
<main class="page-wrap narrow dashboard-page">
    <h1>Customer Dashboard</h1>
    <?php if ($flashSuccess): ?>
    <div class="alert success"><?= e($flashSuccess) ?></div>
<?php endif; ?>
    <div class="dashboard-card">
        <p><strong>Përdoruesi:</strong> <?= e($user->getName()) ?></p>
        <p><strong>Roli:</strong> <?= e($user->getRole()) ?></p>
        <p><strong>Mesazhi:</strong> <?= e($user->getDashboardMessage()) ?></p>
        <p><strong>Menaxhim përdoruesish:</strong> <?= $user->canManageUsers() ? 'Po' : 'Jo' ?></p>
        <p><strong>Last login:</strong> <?= e($_SESSION['last_login'] ?? 'Pa të dhëna') ?></p>
        <p><strong>Favorite destination:</strong> <?= e($user->getFavoriteDestination()) ?></p>
    </div>