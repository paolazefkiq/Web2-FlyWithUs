<?php
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Customer.php';
require_once __DIR__ . '/../classes/Admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$faqItems = [
    [
        'question' => 'A mund të ndryshoj datën e fluturimit?',
        'answer' => 'Po, ju mund të ndryshoni datën deri 24 orë para fluturimit me tarifë të vogël duke kontaktuar shërbimin tonë të klientit.'
    ],
    [
        'question' => 'A mund të marr bagazh ekstra?',
        'answer' => 'Po, mund të shtoni bagazh duke kontaktuar shërbimin tonë të klientit.'
    ],
    [
        'question' => 'A mund të anuloj një fluturim?',
        'answer' => 'Po, anulimet janë të mundshme sipas politikës tonë. Shiko termat dhe kushtet për detaje.'
    ],
    [
        'question' => 'Si mund të marr faturën për biletën?',
        'answer' => 'Faturat gjenerohen automatikisht pas përfundimit të rezervimit dhe dërgohen në email-in tuaj.'
    ],
    [
        'question' => 'A ka pagesa të fshehura?',
        'answer' => 'Jo, të gjitha tarifat dhe taksat shfaqen qartë gjatë rezervimit.'
    ],
    [
        'question' => 'Çfarë ndodh nëse fluturimi anulohet nga linja ajrore?',
        'answer' => 'Nëse fluturimi anulohet, ju do të merrni njoftim me opsion për rimbursim ose rikonfirmim të fluturimit.'
    ],
    [
        'question' => 'A mund të rezervoj më shumë se një biletë njëherësh?',
        'answer' => 'Po, mund të rezervoni deri në 5 bileta në një rezervim.'
    ]
];