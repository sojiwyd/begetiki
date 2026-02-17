<?php
session_start();
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

$date = $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректная дата']);
    exit;
}

$dayStart = $date . ' 09:00:00';
$dayEnd = $date . ' 18:00:00';

$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(appointment_date, '%H:%i') AS slot
    FROM orders
    WHERE appointment_date >= ?
      AND appointment_date < ?
      AND status IN ('new', 'processing')
      AND appointment_date IS NOT NULL
");
$stmt->execute([$dayStart, $dayEnd]);

$busy = [];
while ($row = $stmt->fetch()) {
    $busy[$row['slot']] = true;
}

$slots = [];
for ($h = 9; $h < 18; $h++) {
    foreach ([0, 30] as $m) {
        $slot = sprintf('%02d:%02d', $h, $m);
        $slots[] = [
            'time' => $slot,
            'is_busy' => !empty($busy[$slot]),
        ];
    }
}

echo json_encode([
    'date' => $date,
    'slots' => $slots,
], JSON_UNESCAPED_UNICODE);
