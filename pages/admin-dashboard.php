<?php
require_once __DIR__ . '/../includes/config.php';
requireRole('admin');
$user = currentUser();
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
$flashSuccess = getFlash('flash_success');

$sortedUsers = $dummyUsers;
usort($sortedUsers, fn($a, $b) => strcmp($a['role'], $b['role']));
?>

<main class="page-wrap narrow dashboard-page">
    <h1>Admin Dashboard</h1>
    <?php if ($flashSuccess): ?>
    <div class="alert success"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($user->canManageUsers()): ?>
      <div class="dashboard-card">
        <p><strong>Përdoruesi:</strong> <?= e($user->getName()) ?></p>
        <p><strong>Roli:</strong> <?= e($user->getRole()) ?></p>
        <p><strong>Mesazhi:</strong> <?= e($user->getDashboardMessage()) ?></p>
        <p><strong>Menaxhim përdoruesish:</strong> <?= $user->canManageUsers() ? 'Po' : 'Jo' ?></p>
    </div>
    <?php endif; ?>
<div class="dashboard-card">
        <h3>Përdoruesit statikë</h3>
        <table class="simple-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Emri</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>