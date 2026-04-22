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