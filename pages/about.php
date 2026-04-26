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

$features = [
    [
        'title' => 'Rezervim i shpejtë',
        'text' => 'Plotëso vetëm disa fusha dhe gjej fluturimin ideal në pak sekonda.'
    ],
    [
        'title' => 'Ofertat më të mira',
        'text' => 'Krahasojmë qindra opsione për të gjetur kombinimin më të favorshëm për ju.'
    ],
    [
        'title' => 'Mbështetje njerëzore',
        'text' => 'Ekipi ynë është këtu për t’ju ndihmuar para, gjatë dhe pas fluturimit.'
    ]
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

<section class="about-features">
    <h2>Pse klientët na zgjedhin?</h2>
    <p class="about-features-subtitle">
        Jo vetëm një biletë – por një eksperiencë udhëtimi e plotë.
    </p>

    <div class="about-cards">
        <?php foreach ($features as $feature): ?>
            <div class="about-card">
                <h3><?= e($feature['title']) ?></h3>
                <p><?= e($feature['text']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="about-stats">
    <div class="stat">
        <span class="stat-number">50+</span>
        <span class="stat-label">Destinacione</span>
    </div>
    <div class="stat">
        <span class="stat-number">10K+</span>
        <span class="stat-label">Rezervime</span>
    </div>
    <div class="stat">
        <span class="stat-number">4.9/5</span>
        <span class="stat-label">Kënaqësia e klientëve</span>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>