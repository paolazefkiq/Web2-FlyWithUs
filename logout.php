<?php
require_once __DIR__ . '/includes/config.php';

clearUserSession();

setcookie('preferred_city', '', time() - 3600, '/');

setFlash('flash_success', 'Jeni ckycur me sukses.');

redirect($GLOBALS['base_url'] . '/index.php');