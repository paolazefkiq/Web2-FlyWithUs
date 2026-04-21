<?php
session_start();

// Fshij të gjitha variablat e session-it
$_SESSION = [];

// Shkatërro session-in
session_destroy();

// Fshij cookie-n (nëse ekziston)
if (isset($_COOKIE["last_user"])) {
    setcookie("last_user", "", time() - 3600, "/");
}

// Ridrejto në faqen kryesore
header("Location: index.php");
exit();
?>