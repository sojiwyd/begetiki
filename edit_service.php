<?php
require 'db.php';
require 'check_admin.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    header("Location: admin_services.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    die("Услуга не найдена. <a href='admin_services.php'>← К списку</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = '<div class="alert alert-danger">Ошибка CSRF. Обновите страницу и попробуйте снова.</div>';
    } else {
    $title = trim($_POST['title'] ?? '');
    $price = $_POST['price'] ?? 0;
    $desc = trim($_POST['description'] ?? '');
    $img = trim($_POST['image_url'] ?? '');

    if (empty($title)) {
        $message = '<div class="alert alert-danger">Заполните название!</div>';
    } else {
        $sql = "UPDATE services SET title = ?, description = ?, price = ?, image_url = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $desc, $price ?: 0, $img ?: null, $id]);
        header("Location: admin_services.php?updated=1");
        exit;
    }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать услугу — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1>Редактировать услугу #<?= (int)$id ?></h1>
    <a href="admin_services.php" class="btn btn-secondary mb-3">← Назад</a>
    <?= $message ?? '' ?>
    <form method="POST" class="card p-4">
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
        <div class="mb-3">
            <label class="form-label">Название</label>
            <input type="text" name="title" class="form-control" value="<?= h($service['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Цена (₽)</label>
            <input type="number" name="price" class="form-control" value="<?= h($service['price']) ?>" step="0.01">
        </div>
        <div class="mb-3">
            <label class="form-label">URL картинки</label>
            <input type="text" name="image_url" class="form-control" value="<?= h($service['image_url'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Описание</label>
            <textarea name="description" class="form-control" rows="3"><?= h($service['description'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-warning">Обновить</button>
    </form>
</div>
</body>
</html>
