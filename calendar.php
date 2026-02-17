<?php
session_start();
require 'db.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

$day_start = $date . ' 09:00:00';
$day_end = $date . ' 18:00:00';

// Занятые слоты на эту дату (status = new или processing)
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(appointment_date, '%H:%i') as slot
    FROM orders
    WHERE appointment_date >= ? AND appointment_date < ?
    AND status IN ('new', 'processing')
    AND appointment_date IS NOT NULL
");
$stmt->execute([$day_start, $date . ' 23:59:59']);
$busy_slots = [];
while ($row = $stmt->fetch()) {
    $busy_slots[$row['slot']] = true;
}

// Слоты по 30 минут с 9:00 до 18:00
$slots = [];
for ($h = 9; $h < 18; $h++) {
    foreach ([0, 30] as $m) {
        $slot = sprintf('%02d:%02d', $h, $m);
        $slots[] = $slot;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Календарь занятости — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">Календарь занятости</h4>
            <small class="text-muted">Красный — занято, зелёный — свободно</small>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4 row g-2">
                <div class="col-auto">
                    <input type="date" name="date" class="form-control" value="<?= h($date) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Показать</button>
                </div>
            </form>

            <div class="row g-2">
                <?php foreach ($slots as $slot): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="p-2 rounded text-center text-white <?= !empty($busy_slots[$slot]) ? 'bg-danger' : 'bg-success' ?>">
                        <?= h($slot) ?> — <?= !empty($busy_slots[$slot]) ? 'Занято' : 'Свободно' ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <a href="index.php" class="btn btn-outline-secondary mt-3">← На главную</a>
</div>
</body>
</html>
