<?php $user = currentUser(); ?>
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
        <a href="<?= $GLOBALS['base_url'] ?>/index.php">Ballina</a>
        <a href="<?= $GLOBALS['base_url'] ?>/index.php#booking">Rezervo</a>
        <a href="<?= $GLOBALS['base_url'] ?>/index.php#destinations">Destinacionet</a>
        <a href="<?= $GLOBALS['base_url'] ?>/pages/about.php">Rreth Nesh</a>
        <a href="<?= $GLOBALS['base_url'] ?>/pages/faq.php">FAQ</a>
        <a href="<?= $GLOBALS['base_url'] ?>/pages/contact.php">Kontakti</a>

        <?php if ($user): ?>
            <?php if ($user->getRole() === 'admin'): ?>
                <a href="<?= $GLOBALS['base_url'] ?>/pages/admin-dashboard.php">Admin Dashboard</a>
            <?php else: ?>
                <a href="<?= $GLOBALS['base_url'] ?>/pages/customer-dashboard.php">Customer Dashboard</a>
            <?php endif; ?>
            <a href="<?= $GLOBALS['base_url'] ?>/logout.php">Logout</a>
        <?php else: ?>
            <a href="<?= $GLOBALS['base_url'] ?>/login.php">Login</a>
        <?php endif; ?>
    </div>
</nav>