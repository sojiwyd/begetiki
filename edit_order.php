<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if ($order_id <= 0) {
    header("Location: profile.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT orders.*, services.title, services.price
    FROM orders
    JOIN services ON orders.service_id = services.id
    WHERE orders.id = ? AND orders.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Заказ не найден. <a href='profile.php'>Вернуться</a>");
}

if ($order['status'] !== 'new') {
    die("Нельзя перенести обработанную запись. <a href='profile.php'>Вернуться</a>");
}

if (empty($order['appointment_date'])) {
    die("У этой записи нет даты визита. <a href='profile.php'>Вернуться</a>");
}

$slots = [];
for ($h = 9; $h < 18; $h++) {
    $slots[] = sprintf('%02d:00', $h);
    if ($h < 17) $slots[] = sprintf('%02d:30', $h);
}
$current_date = date('Y-m-d', strtotime($order['appointment_date']));
$current_time = date('H:i', strtotime($order['appointment_date']));
if (!in_array($current_time, $slots)) $current_time = '09:00';
$min_date = (new DateTime())->format('Y-m-d');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die("Ошибка CSRF. <a href='profile.php'>Вернуться</a>");
    }

    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');
    if (empty($appointment_date) || empty($appointment_time) || !in_array($appointment_time, $slots)) {
        $message = '<div class="alert alert-danger">Укажите дату и время.</div>';
    } else {
        $appointment_str = $appointment_date . ' ' . $appointment_time . ':00';
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $appointment_str);
        if (!$dt || $dt <= new DateTime()) {
            $message = '<div class="alert alert-danger">Укажите будущую дату и время.</div>';
            $current_date = $appointment_date;
            $current_time = $appointment_time;
        } else {
            $stmt = $pdo->prepare("UPDATE orders SET appointment_date = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$appointment_str, $order_id, $user_id]);
            header("Location: profile.php?updated=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Перенести запись — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <div class="card shadow-sm" style="max-width: 500px;">
        <div class="card-header"><h5 class="mb-0">Перенести запись #<?= (int)$order_id ?></h5></div>
        <div class="card-body">
            <p class="text-muted"><?= h($order['title']) ?> — <?= number_format((float)$order['price'], 0, '', ' ') ?> ₽</p>
            <?= $message ?>
            <form method="POST">
                <input type="hidden" name="id" value="<?= (int)$order_id ?>">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <div class="mb-3">
                    <label class="form-label">Новая дата визита</label>
                    <input type="date" name="appointment_date" class="form-control" required
                           value="<?= h($current_date) ?>" min="<?= h($min_date) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Время визита</label>
                    <select class="form-select" name="appointment_time" required>
                        <?php foreach ($slots as $slot): ?>
                            <option value="<?= h($slot) ?>" <?= ($slot == $current_time) ? 'selected' : '' ?>><?= h($slot) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-warning">Перенести</button>
                <a href="profile.php" class="btn btn-outline-secondary">Отмена</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
