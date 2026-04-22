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
