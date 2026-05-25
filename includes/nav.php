<?php
$user = currentUser();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$basePath = parse_url($GLOBALS['base_url'], PHP_URL_PATH) ?? '';

if ($basePath !== '' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath));
}

if ($currentPath === '') {
    $currentPath = '/index.php';
}

$isActivePath = static function (array $paths) use ($currentPath): bool {
    return in_array($currentPath, $paths, true);
};

$navClass = static function (array $paths) use ($isActivePath): string {
    return $isActivePath($paths) ? 'active' : '';
};
?>

<nav class="navbar">
    <div class="logo">
        <img src="<?= $GLOBALS['base_url'] ?>/assets/img/airplane-logo.png" alt="Airplane Logo" class="nav-logo">
        <a href="<?= $GLOBALS['base_url'] ?>/index.php" class="logo-text">Fly With Us</a>
    </div>

    <div class="hamburger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="nav-links" id="navLinks">
        <a class="<?= $navClass(['/index.php']) ?>" href="<?= $GLOBALS['base_url'] ?>/index.php">Ballina</a>
        <a class="<?= $navClass(['/pages/booking.php', '/booking.php']) ?>" href="<?= $GLOBALS['base_url'] ?>/pages/booking.php">Rezervo</a>
        <a class="<?= $navClass(['/pages/about.php']) ?>" href="<?= $GLOBALS['base_url'] ?>/pages/about.php">Rreth Nesh</a>
        <a class="<?= $navClass(['/pages/faq.php']) ?>" href="<?= $GLOBALS['base_url'] ?>/pages/faq.php">FAQ</a>
        <a class="<?= $navClass(['/pages/contact.php']) ?>" href="<?= $GLOBALS['base_url'] ?>/pages/contact.php">Kontakti</a>

        <?php if ($user): ?>
            <?php if ($user->getRole() === 'admin'): ?>
                <a class="<?= $navClass(['/pages/admin-dashboard.php', '/pages/admin-destinations.php', '/pages/admin-origin-cities.php', '/pages/admin-routes.php']) ?>" href="<?= $GLOBALS['base_url'] ?>/pages/admin-dashboard.php">Admin Dashboard</a>
            <?php else: ?>
                <a class="<?= $navClass(['/pages/customer-dashboard.php']) ?>" href="<?= $GLOBALS['base_url'] ?>/pages/customer-dashboard.php#reservations">Dashboard</a>
            <?php endif; ?>
            <a href="<?= $GLOBALS['base_url'] ?>/logout.php">Logout</a>
        <?php else: ?>
            <a class="<?= $navClass(['/login.php']) ?>" href="<?= $GLOBALS['base_url'] ?>/login.php">Login</a>
        <?php endif; ?>
    </div>
</nav>