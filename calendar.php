<?php
session_start();
require 'db.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

$day_start = $date . ' 09:00:00';
$day_end = $date . ' 18:00:00';

$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(appointment_date, '%H:%i') as slot
    FROM orders
    WHERE appointment_date >= ? AND appointment_date < ?
    AND status IN ('new', 'processing')
    AND appointment_date IS NOT NULL
");
$stmt->execute([$day_start, $day_end]);
$busy_slots = [];
while ($row = $stmt->fetch()) {
    $busy_slots[$row['slot']] = true;
}

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
            <form method="GET" class="mb-4 row g-2" id="calendarFilterForm">
                <div class="col-auto">
                    <input type="date" id="calendarDateInput" name="date" class="form-control" value="<?= h($date) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Показать</button>
                </div>
            </form>

            <div class="row g-2" id="slotsGrid">
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
<script>
(function () {
    const form = document.getElementById('calendarFilterForm');
    const dateInput = document.getElementById('calendarDateInput');
    const grid = document.getElementById('slotsGrid');

    if (!form || !dateInput || !grid) return;

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderSlots(slots) {
        const html = slots.map((slot) => {
            const busy = Boolean(slot.is_busy);
            const className = busy ? 'bg-danger' : 'bg-success';
            const label = busy ? 'Занято' : 'Свободно';
            return `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="p-2 rounded text-center text-white ${className}">
                        ${escapeHtml(slot.time)} — ${label}
                    </div>
                </div>
            `;
        }).join('');

        grid.innerHTML = html || '<div class="text-muted">Нет данных.</div>';
    }

    async function loadSlots() {
        const date = dateInput.value;
        if (!date) return;
        const response = await fetch('api/get_slots.php?date=' + encodeURIComponent(date));
        if (!response.ok) return;
        const data = await response.json();
        if (!data || !Array.isArray(data.slots)) return;
        renderSlots(data.slots);
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        loadSlots();
    });

    loadSlots();
})();
</script>
</body>
</html>
