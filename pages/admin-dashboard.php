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
        <h3>Informatat për përdoruesin </h3>
        <p><strong>Përdoruesi:</strong> <?= e($user->getName()) ?></p>
        <p><strong>Roli:</strong> <?= e($user->getRole()) ?></p>
        <p><strong>Përshkrimi:</strong> <?= e($user->getDashboardMessage()) ?></p>
        <p><strong>Menaxhim përdoruesish:</strong> <?= $user->canManageUsers() ? 'Po' : 'Jo' ?></p>
    </div>
    <?php endif; ?>

<?php $contactMessage = $_SESSION['contact_message'] ?? null; ?>

<?php if ($contactMessage): ?>
    <div class="dashboard-card">
        <h3>Mesazhi i fundit nga forma e kontaktit</h3>
        <p><strong>Emri:</strong> <?= e($contactMessage['name']) ?></p>
        <p><strong>Email:</strong> <?= e($contactMessage['email']) ?></p>
        <p><strong>Subjekti:</strong> <?= e($contactMessage['subject']) ?></p>
        <p><strong>Mesazhi:</strong> <?= e($contactMessage['message']) ?></p>
        <p><strong>Dërguar më:</strong> <?= e($contactMessage['sent_at']) ?></p>
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
                <?php foreach ($sortedUsers as $dummy): ?>
                    <tr>
                        <td><?= e((string)$dummy['id']) ?></td>
                        <td><?= e($dummy['name']) ?></td>
                        <td><?= e($dummy['email']) ?></td>
                        <td><?= e($dummy['role']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
