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
$GLOBALS['base_url'] = '/Web2-FlyWithUs';

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

$dummyUsers = [
    [
        'id' => 1,
        'name' => 'Customer FlyWithUs',
        'email' => 'customer@flywithus.com',
        'username' => 'customer1',
        'password' => 'Customer123',
        'role' => 'customer',
        'favoriteDestination' => 'Paris'
    ],
    [
        'id' => 2,
        'name' => 'Admin FlyWithUs',
        'email' => 'admin@flywithus.com',
        'username' => 'admin1',
        'password' => 'Admin123',
        'role' => 'admin',
        'favoriteDestination' => 'Tokyo'
    ]
];

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header("Location: " . $path);
    exit;
}

function currentUser(): ?User
{
    if (!isset($_SESSION['user'])) {
        return null;
    }

    if ($_SESSION['user'] instanceof User) {
        return $_SESSION['user'];
    }

    unset($_SESSION['user']);
    return null;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']) && $_SESSION['user'] instanceof User;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Duhet të kyçeni fillimisht.';
        redirect($GLOBALS['base_url'] . '/login.php');
    }
}

function requireRole(string $role): void
{
    requireLogin();
    $user = currentUser();
    if (!$user || $user->getRole() !== $role) {
        $_SESSION['flash_error'] = 'Nuk keni qasje në këtë faqe.';
        redirect($GLOBALS['base_url'] . '/index.php');
    }
}

function getFlash(string $key): ?string
{
    if (!isset($_SESSION[$key])) {
        return null;
    }
    $message = $_SESSION[$key];
    unset($_SESSION[$key]);
    return $message;
}

function sortDestinationsByPrice(array $destinations): array
{
    usort($destinations, function ($a, $b) {
        return $a['price'] <=> $b['price'];
    });
    return $destinations;
}

function findUserByLogin(string $login, string $password, array $users): ?array
{
    foreach ($users as $user) {
        if (($user['email'] === $login || $user['username'] === $login) && $user['password'] === $password) {
            return $user;
        }
    }
    return null;
}

function buildUserObject(array $userData): User
{
    if ($userData['role'] === 'admin') {
        $user = new Admin(
            $userData['id'],
            $userData['name'],
            $userData['email'],
            $userData['username'],
            $userData['role'],
            $userData['favoriteDestination']
        );
    } else {
        $user = new Customer(
            $userData['id'],
            $userData['name'],
            $userData['email'],
            $userData['username'],
            $userData['role'],
            $userData['favoriteDestination']
        );
    }
    return $user;
}
