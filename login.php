<?php
require_once __DIR__ . '/includes/config.php';

$redirectAfterLogin = sanitizeInternalRedirect($_GET['redirect'] ?? '');

if (isLoggedIn()) {
    $user = currentUser();

    if ($user->getRole() === 'customer' && $redirectAfterLogin !== '') {
        redirect($redirectAfterLogin);
    }
 redirect(
        $user->getRole() === 'admin'
            ? $GLOBALS['base_url'] . '/pages/admin-dashboard.php'
            : $GLOBALS['base_url'] . '/pages/customer-dashboard.php'
    );
}
$old = [
    'login' => '',
];

$errors = [
    'login' => '',
    'password' => '',
    'general' => '',
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectAfterLogin = sanitizeInternalRedirect($_POST['post_login_redirect'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $old['login'] = $login;
    $errors['login'] = validateLoginValue($login);

    if ($password === '') {
        $errors['password'] = 'Ju lutem plotesoni fjalekalimin.';
    }
    if (!array_filter([$errors['login'], $errors['password']])) {
        try {
            $pdo = getPDO();
            $statement = $pdo->prepare(
                'SELECT id, full_name, email, username, password_hash, role
                 FROM users
                 WHERE email = :email_login OR username = :username_login
                 LIMIT 1'
            );
            $statement->execute([
                'email_login' => $login,
                'username_login' => $login,
            ]);

            $matchedUser = $statement->fetch();

            if ($matchedUser && password_verify($password, $matchedUser['password_hash'])) {
                session_regenerate_id(true);
storeUserSession([
                    'id' => (int)$matchedUser['id'],
                    'name' => $matchedUser['full_name'],
                    'email' => $matchedUser['email'],
                    'username' => $matchedUser['username'],
                    'role' => $matchedUser['role'],
                ]);
                $_SESSION['last_login'] = date('Y-m-d H:i:s');

                $updateStatement = $pdo->prepare(
                    'UPDATE users SET last_login_at = NOW() WHERE id = :id'
                );
                $updateStatement->execute(['id' => $matchedUser['id']]);

                setFlash('flash_success', 'Jeni kycur me sukses.');

                if ($matchedUser['role'] === 'customer' && $redirectAfterLogin !== '') {
                    redirect($redirectAfterLogin);
                }
                redirect(
                    $matchedUser['role'] === 'admin'
                        ? $GLOBALS['base_url'] . '/pages/admin-dashboard.php'
                        : $GLOBALS['base_url'] . '/pages/customer-dashboard.php'
                );
            }
             $errors['general'] = 'Email ose fjalekalim i pasakte.';
        } catch (PDOException $exception) {
            $errors['general'] = 'Ndodhi nje gabim. Ju lutemi provoni perseri me vone.';
        }
    }
}
