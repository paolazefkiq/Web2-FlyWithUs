<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?><?= e($GLOBALS['site_name']) ?></title>
    <link rel="stylesheet" href="<?= $GLOBALS['base_url'] ?>/assets/css/style.css">
</head>
<body>