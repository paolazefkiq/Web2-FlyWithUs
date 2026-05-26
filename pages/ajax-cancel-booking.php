<?php

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=UTF-8');

function sendJson(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}
