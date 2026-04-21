<?php
require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) {
    $user = currentUser();
    redirect($user->getRole() === 'admin'
    ? $GLOBALS['base_url'] . '/pages/admin-dashboard.php'
    : $GLOBALS['base_url'] . '/pages/customer-dashboard.php'
);
}

$old = [
    'login' => ''
];

$errors = [
    'login' => '',
    'password' => '',
    'general' => ''
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $old['login'] = $login;

    $emailPattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    $usernamePattern = '/^[A-Za-z0-9_]{4,20}$/';
     if ($login === '') {
        $errors['login'] = 'Ju lutem plotësoni email-in ose username-in.';
    } elseif (!preg_match($emailPattern, $login) && !preg_match($usernamePattern, $login)) {
        $errors['login'] = 'Login duhet të jetë email i vlefshëm ose username me 4-20 karaktere.';
    }

    if ($password === '') {
        $errors['password'] = 'Ju lutem plotësoni fjalëkalimin.';
    }

    if (!array_filter([
        $errors['login'],
        $errors['password']
    ])) {
        $matchedUser = findUserByLogin($login, $password, $dummyUsers);

         if ($matchedUser) {
            $userObject = buildUserObject($matchedUser);

            $_SESSION['user'] = $userObject;
            $_SESSION['role'] = $userObject->getRole();
            $_SESSION['last_login'] = date('Y-m-d H:i:s');

              setcookie(
                'preferred_city',
                $userObject->getFavoriteDestination(),
                time() + 60 * 60 * 24 * 30,
                '/'
            );
 $_SESSION['flash_success'] = 'Jeni kyçur me sukses.';
            redirect($userObject->getRole() === 'admin'
    ? $GLOBALS['base_url'] . '/pages/admin-dashboard.php'
    : $GLOBALS['base_url'] . '/pages/customer-dashboard.php'
);
        } else {
            $errors['general'] = 'Kredencialet nuk janë të sakta.';
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/nav.php';
?>
<main class="page-wrap narrow">
    <section class="about-form single-box">
        <div class="about-form-box" id="about-form-box">
            <h3>Login</h3>
            <p class="demo-note">
            Customer: customer@flywithus.com ose customer1 / Customer123<br>
            Admin: admin@flywithus.com ose admin1 / Admin123
            </p>
