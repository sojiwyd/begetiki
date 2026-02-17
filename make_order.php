<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Сначала войдите в систему! <a href='login.php'>Вход</a>");
}

$service_id = (int)($_GET['id'] ?? $_POST['service_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if ($service_id <= 0) {
    die("Неверная услуга. <a href='index.php'>Вернуться</a>");
}

$check = $pdo->prepare("SELECT id, title, price FROM services WHERE id = ?");
$check->execute([$service_id]);
$service = $check->fetch();

if (!$service) {
    die("Ошибка: Услуга не найдена! <a href='index.php'>Вернуться</a>");
}

// Слоты каждые 30 минут (9:00 - 17:00)
$slots = [];
for ($h = 9; $h < 18; $h++) {
    $slots[] = sprintf('%02d:00', $h);
    if ($h < 17) $slots[] = sprintf('%02d:30', $h);
}

// POST — создание заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');
    if (empty($appointment_date) || empty($appointment_time) || !in_array($appointment_time, $slots)) {
        die("Укажите дату и время визита. <a href='make_order.php?id=" . $service_id . "'>Назад</a>");
    }

    $appointment_str = $appointment_date . ' ' . $appointment_time . ':00';
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $appointment_str);
    if (!$dt || $dt <= new DateTime()) {
        die("Укажите будущую дату и время. <a href='make_order.php?id=" . $service_id . "'>Назад</a>");
    }

    $recent = $pdo->prepare("
        SELECT id FROM orders 
        WHERE user_id = ? AND service_id = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        LIMIT 1
    ");
    $recent->execute([$user_id, $service_id]);
    if ($recent->fetch()) {
        die("Вы уже записывались на эту услугу недавно. Подождите 5 минут. <a href='index.php'>Вернуться</a>");
    }

    // Загрузка фото повреждения
    $damage_photo_url = null;
    $uploadDir = __DIR__ . '/uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (isset($_FILES['damage_photo']) && $_FILES['damage_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['damage_photo'];
        if (!in_array($file['type'], $allowedTypes)) {
            die("Ошибка: Можно загружать только JPG, PNG, GIF. <a href='make_order.php?id=" . $service_id . "'>Назад</a>");
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'jpg';
        $newName = 'damage_' . uniqid() . '.' . $ext;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
            $damage_photo_url = 'uploads/' . $newName;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, service_id, appointment_date, damage_photo_url) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$user_id, $service_id, $appointment_str, $damage_photo_url]);
        echo "Запись успешно оформлена на " . $dt->format('d.m.Y H:i') . "! " . ($damage_photo_url ? "Фото повреждения загружено. " : "") . "<a href='profile.php'>Мои заказы</a> | <a href='index.php'>На главную</a>";
    } catch (PDOException $e) {
        echo "Ошибка: " . h($e->getMessage()) . " <a href='index.php'>Вернуться</a>";
    }
    exit;
}

$min_date = (new DateTime())->format('Y-m-d');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Записаться — <?= h($service['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-light bg-light mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Автосервис</a>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">← Назад</a>
    </div>
</nav>
<div class="container">
    <div class="card shadow-sm" style="max-width: 500px;">
        <div class="card-header"><h5 class="mb-0">Записаться на <?= h($service['title']) ?></h5></div>
        <div class="card-body">
            <p class="text-muted"><?= number_format((float)$service['price'], 0, '', ' ') ?> ₽</p>
            <form method="POST" action="make_order.php" enctype="multipart/form-data">
                <input type="hidden" name="service_id" value="<?= (int)$service_id ?>">
                <div class="mb-3">
                    <label for="appointment_date" class="form-label">Дата визита</label>
                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" required min="<?= h($min_date) ?>">
                </div>
                <div class="mb-3">
                    <label for="appointment_time" class="form-label">Время визита</label>
                    <select class="form-select" id="appointment_time" name="appointment_time" required>
                        <?php foreach ($slots as $slot): ?>
                            <option value="<?= h($slot) ?>"><?= h($slot) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="damage_photo" class="form-label">Фото повреждения автомобиля</label>
                    <input type="file" name="damage_photo" id="damage_photo" class="form-control" accept="image/jpeg,image/png,image/gif">
                    <small class="text-muted">JPG, PNG или GIF</small>
                </div>
                <button type="submit" class="btn btn-primary">Записаться</button>
                <a href="index.php" class="btn btn-outline-secondary">Отмена</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
