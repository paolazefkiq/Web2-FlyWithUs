<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'FAQ';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/nav.php';
?>

<section class="about-hero-bg">
    <div class="hero-overlay">
        <h1>Pyetje të Shpeshta</h1>
        <p class="about-lead">
            Këtu mund të gjeni përgjigje për pyetjet më të zakonshme rreth fluturimeve dhe rezervimeve.
        </p>
    </div>
</section>

<section class="faq-section">
    <div class="faq-cards">
        <?php foreach ($faqItems as $item): ?>
            <div class="faq-card">
                <h3><?= e($item['question']) ?></h3>
                <p><?= e($item['answer']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
