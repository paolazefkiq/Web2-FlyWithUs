<?php
$pageTitle = 'Ballina';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';

// Marrja e te dhenave
$sortedDestinations = sortDestinationsByPrice($destinations);
$flashSuccess = getFlash('flash_success');
$flashError = getFlash('flash_error');
$popupSuccess = $_SESSION['popup_success'] ?? '';
unset($_SESSION['popup_success']);
$preferredCity = $_COOKIE['preferred_city'] ?? '';

$old = [
    'name' => '',
    'email' => '',
    'from' => '',
    'to' => '',
    'depart' => '',
    'return' => '',
    'passengers' => '1'
];

$errors = [
    'name' => '',
    'email' => '',
    'from' => '',
    'to' => '',
    'depart' => '',
    'return' => '',
    'passengers' => ''
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $value) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    $namePattern = '/^[A-Za-zÇçËëÁÉÍÓÚáéíóúÄÖÜäöü\s]{2,50}$/u';
    $emailPattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

    if ($old['name'] === '') {
    $errors['name'] = 'Ju lutem plotësoni emrin.';
} elseif (!preg_match($namePattern, $old['name'])) {
    $errors['name'] = 'Emri duhet të ketë vetëm shkronja dhe së paku 2 karaktere.';
}

if ($old['email'] === '') {
    $errors['email'] = 'Ju lutem plotësoni email-in.';
} elseif (!preg_match($emailPattern, $old['email'])) {
    $errors['email'] = 'Email nuk është valid.';
}

if ($old['from'] === '') {
        $errors['from'] = 'Zgjidh qytetin e nisjes.';
    }

    if ($old['to'] === '') {
        $errors['to'] = 'Zgjidh destinacionin.';
    } elseif ($old['from'] !== '' && (!isset($flightMatrix[$old['from']]) || !isset($flightMatrix[$old['from']][$old['to']]))) {
        $errors['to'] = 'Zgjedhja e fluturimit nuk është valide.';
    }

    if ($old['depart'] === '') {
        $errors['depart'] = 'Data e nisjes është e detyrueshme.';
    }

    if ($old['return'] !== '' && $old['depart'] !== '' && $old['return'] < $old['depart']) {
        $errors['return'] = 'Data e kthimit nuk mund të jetë para nisjes.';
    }

    $passengersNumber = (int)$old['passengers'];
    if ($passengersNumber < 1 || $passengersNumber > 5) {
        $errors['passengers'] = 'Numri i pasagjerëve duhet të jetë nga 1 deri në 5.';
    }

     if (!array_filter($errors)) {
        $basePrice = $flightMatrix[$old['from']][$old['to']];
        $total = $basePrice * $passengersNumber;

        if ($old['return'] !== '') {
            $total *= 2;
        }

        $_SESSION['last_booking'] = [
            'name' => $old['name'],
            'route' => $old['from'] . ' - ' . $old['to'],
            'total' => $total,
            'date' => $old['depart']
        ];

        setcookie('preferred_city', $old['to'], time() + 60 * 60 * 24 * 30, '/');

$_SESSION['popup_success'] = "
    Rezervimi është bërë me sukses, <strong>" . e($old['name']) . "</strong>!<br><br>
    Ju do të udhëtoni nga <strong>" . e($old['from']) . "</strong> drejt <strong>" . e($old['to']) . "</strong>
    për <strong>" . e($old['passengers']) . "</strong> pasagjer(ë) më datën
    <span style='white-space: nowrap;'><strong>" . e($old['depart']) . "</strong></span>" .
    ($old['return'] !== ''
        ? " dhe kthim më <span style='white-space: nowrap;'><strong>" . e($old['return']) . "</strong></span>."
        : ".") .
    "<br><br>
    Ne do t'ju kontaktojmë së shpejti në <strong>" . e($old['email']) . "</strong>.<br>
    Totali i rezervimit: <strong>$" . e((string)$total) . "</strong>.
";
?>