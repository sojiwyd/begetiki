<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../src/Core/Helpers.php';
require_once __DIR__ . '/../../src/Core/Database.php';
require_once __DIR__ . '/../../src/Models/Order.php';

header('Content-Type: application/json; charset=utf-8');

$date = (string)($_GET['date'] ?? date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректная дата'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'date' => $date,
    'slots' => (new Order())->getBusySlots($date),
], JSON_UNESCAPED_UNICODE);
