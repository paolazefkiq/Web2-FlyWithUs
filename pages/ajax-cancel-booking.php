<?php

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=UTF-8');

function sendJson(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}
$user = currentUser();

if (!$user || $user->getRole() !== 'customer') {
    sendJson([
        'success' => false,
        'message' => 'Nuk keni qasje ne kete veprim.',
    ], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson([
        'success' => false,
        'message' => 'Kerkesa nuk u pranua.',
    ], 405);
}

$bookingId = (int)($_POST['booking_id'] ?? 0);

if ($bookingId < 1) {
    sendJson([
        'success' => false,
        'message' => 'Rezervimi nuk u gjet.',
    ], 422);
}