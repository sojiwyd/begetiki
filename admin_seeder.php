<?php
session_start();
require 'db.php';
require 'check_admin.php';

$message = "";
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$tables = [];
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("CSRF Attack blocked. <a href='admin_seeder.php'>Вернуться</a>");
    }

    $tableName = $_POST['table_name'];
    $count = min(1000, max(1, (int)$_POST['count']));

    if (!in_array($tableName, $tables)) {
        die("Ошибка: Таблица не найдена. <a href='admin_seeder.php'>Вернуться</a>");
    }

    $exportDir = __DIR__ . '/exports/';
    if (!is_dir($exportDir)) mkdir($exportDir, 0755, true);

    $filename = $exportDir . $tableName . '_' . date('Y-m-d_H-i-s') . '.csv';
    $fp = fopen($filename, 'w');

    $stmt = $pdo->query("SELECT * FROM `$tableName`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        $message = "Таблица пуста! Сначала создайте хотя бы одну запись вручную.";
    } else {
        fputcsv($fp, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        $message .= "Бэкап сохранён: " . basename($filename) . "<br>";

        $template = $rows[array_rand($rows)];
        $inserted = 0;

        for ($i = 0; $i < $count; $i++) {
            $newRow = [];
            foreach ($template as $key => $value) {
                if ($key === 'id') continue;

                if (is_numeric($value) && $value !== '') {
                    if (strpos($key, '_id') !== false || $key === 'user_id' || $key === 'service_id') {
                        $newValue = $value;
                    } else {
                        $percent = mt_rand(-15, 15) / 100;
                        $newValue = round($value * (1 + $percent), 2);
                    }
                } else {
                    $newValue = $value . "_" . mt_rand(1000, 9999);
                }
                $newRow[$key] = $newValue;
            }

            $cols = array_keys($newRow);
            $placeholders = array_fill(0, count($cols), '?');
            $sql = "INSERT INTO `$tableName` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $placeholders) . ")";

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_values($newRow));
                $inserted++;
            } catch (Exception $e) {
                continue;
            }
        }
        $message .= "Сгенерировано записей: $inserted из $count.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Генератор данных — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5 bg-light">
<div class="container">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">⚙️ Генератор контента (Seeder)</h3>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-info"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <div class="mb-3">
                    <label class="form-label">Таблица для наполнения:</label>
                    <select name="table_name" class="form-select">
                        <?php foreach ($tables as $t): ?>
                            <option value="<?= h($t) ?>" <?= ($t === 'services') ? 'selected' : '' ?>><?= h($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Рекомендуется services — для проверки пагинации.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Сколько записей добавить?</label>
                    <input type="number" name="count" class="form-control" value="100" min="1" max="1000">
                    <small class="text-muted">Для стресс-теста: 100, 500 или 1000.</small>
                </div>

                <div class="alert alert-warning">
                    <small>⚠️ Скрипт создаст CSV-бэкап в /exports, затем скопирует записи с изменением цен ±15%.</small>
                </div>

                <button type="submit" class="btn btn-success w-100">🚀 Наполнить и Бэкапить</button>
            </form>

            <a href="admin_panel.php" class="btn btn-secondary mt-3">← В админку</a>
        </div>
    </div>
</div>
</body>
</html>
