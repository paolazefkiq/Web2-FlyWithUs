<?php
require_once __DIR__ . '/includes/config.php';

$currentUser = currentUser();
$destinationsError = '';
$destinations = [];
$preferredCity = $_COOKIE['preferred_city'] ?? '';
$flashSuccess = getFlash('flash_success');
$flashError = getFlash('flash_error');

try {
    $pdo = getPDO();
    $destinationsStatement = $pdo->prepare(
        'SELECT d.id, d.city, d.country, d.description, d.image_path, MIN(r.price) AS min_price
         FROM destinations d
         INNER JOIN routes r ON r.destination_id = d.id
         GROUP BY d.id, d.city, d.country, d.description, d.image_path
         ORDER BY d.city ASC'
    );
    $destinationsStatement->execute();
    $destinations = $destinationsStatement->fetchAll();

    usort($destinations, static function (array $left, array $right): int {
        $leftPrice = (float)$left['min_price'];
        $rightPrice = (float)$right['min_price'];

        if ($leftPrice === $rightPrice) {
            return strcasecmp((string)$left['city'], (string)$right['city']);
        }

        return $leftPrice <=> $rightPrice;
    });
} catch (PDOException $exception) {
    $destinationsError = 'Ndodhi nje gabim. Ju lutemi provoni perseri me vone.';
}

$pageTitle = 'Ballina';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>

<header class="hero" id="hero">
    <div class="hero-overlay">
        <h1 class="hero-title">Fly With Us</h1>
        <p class="hero-subtitle">
            Zbuloni destinacione te bukura, shikoni ofertat dhe kaloni te rezervimi vetem kur te jeni gati.
        </p>
        <div class="hero-actions">
            <a href="#destinations" class="btn-primary">Shiko Ofertat</a>
        </div>
    </div>
</header>

<main>
    <?php if ($flashSuccess): ?>
        <div class="alert success"><?= e($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert error"><?= e($flashError) ?></div>
    <?php endif; ?>

    <section class="info-strip">
        <div>
            <strong>Cookie preference:</strong>
            <?php if ($preferredCity !== ''): ?>
                Destinacioni juaj i fundit i ruajtur eshte <strong><?= e($preferredCity) ?></strong>.
            <?php else: ?>
                Nuk ka ende destinacion te ruajtur ne cookie.
            <?php endif; ?>
        </div>

        <?php if ($currentUser): ?>
            <div>
                <strong>Session:</strong> <?= e($currentUser->getGreeting()) ?>
            </div>
        <?php else: ?>
            <div>
                <strong>Demo login:</strong>
                customer1, customer2 / Customer123
                dhe
                admin@flywithus.com ose admin1 / Admin123
            </div>
        <?php endif; ?>
    </section>

    <section class="destinations" id="destinations">
        <h2>Destinacionet dhe Ofertat</h2>

        <?php if ($destinationsError): ?>
            <div class="alert error"><?= e($destinationsError) ?></div>
        <?php elseif (!$destinations): ?>
            <div class="alert error">Aktualisht nuk ka destinacione te disponueshme.</div>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($destinations as $destination): ?>
                    <?php
                    $imagePath = buildDestinationImagePath($destination);
                    $imageUrl = $GLOBALS['base_url'] . '/' . ltrim($imagePath, '/');
                    $fallbackImageUrl = $GLOBALS['base_url'] . '/assets/img/airplane-bg.jpg';
                    ?>
                    <article class="card">
                        <img
                            src="<?= e($imageUrl) ?>"
                            alt="<?= e($destination['city']) ?>"
                            onerror="this.onerror=null;this.src='<?= e($fallbackImageUrl) ?>';"
                        >
                        <div class="card-content">
                            <span class="card-eyebrow"><?= e($destination['country']) ?></span>
                            <h3><?= e($destination['city']) ?></h3>
                            <p>
                                <?= e($destination['description']) ?><br>
                                <span class="card-price">Duke filluar nga <strong>$<?= e(number_format((float)$destination['min_price'], 0)) ?></strong></span>
                            </p>
                            <a
                                href="<?= $GLOBALS['base_url'] ?>/pages/booking.php?destination_id=<?= e((string)$destination['id']) ?>"
                                class="card-btn"
                            >
                                Shiko oferta
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>