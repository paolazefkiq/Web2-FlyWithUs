<?php
// index.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/destinations.php'; 

$currentUser = currentUser();
$destinationsError = '';
$destinations = [];
$preferredCity = $_COOKIE['preferred_city'] ?? '';
$flashSuccess = getFlash('flash_success');
$flashError = getFlash('flash_error');

try {
    $pdo = getPDO();
    $destinations = get_sorted_destinations($pdo); 
} catch (PDOException $exception) {
    $destinationsError = 'Ndodhi nje gabim. Ju lutemi provoni perseri me vone.';
}

$pageTitle = 'Ballina';
require_once __DIR__ . '/views/index.view.php';

<?php

function get_sorted_destinations(PDO $pdo): array 
{
    $statement = $pdo->prepare(
        'SELECT d.id, d.city, d.country, d.description, d.image_path, MIN(r.price) AS min_price
         FROM destinations d
         INNER JOIN routes r ON r.destination_id = d.id
         GROUP BY d.id, d.city, d.country, d.description, d.image_path
         ORDER BY d.city ASC'
    );
    $statement->execute();
    $destinations = $statement->fetchAll();

    usort($destinations, static function (array $left, array $right): int {
        $leftPrice = (float)$left['min_price'];
        $rightPrice = (float)$right['min_price'];

        if ($leftPrice === $rightPrice) {
            return strcasecmp((string)$left['city'], (string)$right['city']);
        }

        return $leftPrice <=> $rightPrice;
    });

    return $destinations;
}
<?php

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
?>

<header class="hero" id="hero">
    <div class="hero-overlay">
        <h1 class="hero-title">Fly With Us</h1>
        <p class="hero-subtitle">Zbuloni destinacione te bukura, shikoni ofertat...</p>
        <div class="hero-actions"><a href="#destinations" class="btn-primary">Shiko Ofertat</a></div>
    </div>
</header>

<main>
    <?php if ($flashSuccess): ?><div class="alert success"><?= e($flashSuccess) ?></div><?php endif; ?>
    <?php if ($flashError): ?><div class="alert error"><?= e($flashError) ?></div><?php endif; ?>

    <section class="info-strip">
        <div>Cookie preference: <strong><?= $preferredCity ? e($preferredCity) : 'Nuk ka ruajtur' ?></strong></div>
        <div>Session: <?= $currentUser ? e($currentUser->getGreeting()) : 'Demo login' ?></div>
    </section>

    <section class="destinations" id="destinations">
        <h2>Destinacionet dhe Ofertat</h2>
        <?php if ($destinationsError): ?>
            <div class="alert error"><?= e($destinationsError) ?></div>
        <?php elseif (!$destinations): ?>
            <div class="alert error">Aktualisht nuk ka destinacione.</div>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($destinations as $destination): ?>
                    <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>