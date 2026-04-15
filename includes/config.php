<?php
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Customer.php';
require_once __DIR__ . '/../classes/Admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}