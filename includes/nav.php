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