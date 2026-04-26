<?php
require_once __DIR__ . '/includes/config.php';

$_SESSION = [];
$_SESSION['flash_success'] = 'Jeni çkyçur me sukses.';

setcookie('preferred_city', '', time() - 3600, '/');

header('Location: ' . $GLOBALS['base_url'] . '/index.php');
exit;
?>