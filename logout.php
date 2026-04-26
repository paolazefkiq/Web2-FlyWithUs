<?php
require_once __DIR__ . '/includes/config.php';

session_start();

// Ruaj mesazhin para se te pastrohet session-i
$flashSuccess = 'Jeni çkyçur me sukses.';

// Fshij të gjitha variablat e session-it
$_SESSION = [];

// Shkatërro session-in
session_destroy();

// Fshij cookie-t
setcookie('last_user', '', time() - 3600, '/');
setcookie('preferred_city', '', time() - 3600, '/');

// Nise session te ri vetem per flash message
session_start();
$_SESSION['flash_success'] = $flashSuccess;

// Ridrejto në faqen kryesore
header("Location: index.php");
exit();
?>