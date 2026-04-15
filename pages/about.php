<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Rreth Nesh';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';

$heroPoints = [
    'Partneritete me linja ajrore të besuara',
    'Çmime transparente me oferta sezonale',
    'Mbështetje 24/7 për çdo pyetje ose ndryshim bilete'
];
?>

<section class="about-hero-bg">
    <div class="hero-overlay">
        <span class="about-badge">Kush jemi</span>
        <h1>Platforma juaj e besuar për fluturime të lehta dhe të sigurta</h1>
        <p class="about-lead">
            Fly With Us është një platformë moderne për rezervimin e fluturimeve.
        </p>

        <ul class="about-list">
            <?php foreach ($heroPoints as $point): ?>
                <li><?= e($point) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>