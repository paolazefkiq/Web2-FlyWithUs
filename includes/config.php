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

$GLOBALS['site_name'] = 'Fly With Us';
$GLOBALS['support_email'] = 'support@flywithus.com';
$GLOBALS['support_phone'] = '+383 49 123 456';
$GLOBALS['base_url'] = '/FlyWithUsPhase1';

$destinations = [
    ['city' => 'New York', 'price' => 399, 'type' => 'Long Haul'],
    ['city' => 'Paris', 'price' => 299, 'type' => 'City Break'],
    ['city' => 'Tokyo', 'price' => 749, 'type' => 'Long Haul'],
    ['city' => 'Dubai', 'price' => 499, 'type' => 'Luxury'],
    ['city' => 'Berlin', 'price' => 279, 'type' => 'City Break'],
    ['city' => 'London', 'price' => 350, 'type' => 'City Break'],
    ['city' => 'Rome', 'price' => 320, 'type' => 'Culture']
];

$flightMatrix = [
    'Prishtina' => ['New York' => 399, 'Paris' => 299, 'Tokyo' => 749, 'Dubai' => 499, 'Berlin' => 279, 'London' => 350, 'Rome' => 320],
    'Tirana' => ['New York' => 420, 'Paris' => 310, 'Tokyo' => 770, 'Dubai' => 520, 'Berlin' => 290, 'London' => 360, 'Rome' => 330],
    'Shkup' => ['New York' => 430, 'Paris' => 320, 'Tokyo' => 780, 'Dubai' => 530, 'Berlin' => 295, 'London' => 370, 'Rome' => 335],
    'Podgorica' => ['New York' => 450, 'Paris' => 340, 'Tokyo' => 800, 'Dubai' => 550, 'Berlin' => 310, 'London' => 380, 'Rome' => 350],
    'Sarajevo' => ['New York' => 440, 'Paris' => 330, 'Tokyo' => 790, 'Dubai' => 540, 'Berlin' => 305, 'London' => 375, 'Rome' => 345]
];