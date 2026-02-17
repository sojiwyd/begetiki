<?php
require 'check_admin.php';
require 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление услугами — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1>Управление услугами</h1>
    <?php if (!empty($_GET['updated'])): ?>
        <div class="alert alert-success py-2 mb-2">Услуга обновлена.</div>
    <?php endif; ?>
    <a href="admin_panel.php" class="btn btn-secondary mb-2">← В админку</a>
    <a href="add_item.php" class="btn btn-success mb-2">+ Добавить услугу</a>
    <a href="index.php" class="btn btn-outline-primary mb-2">На главную</a>

    <table class="table table-bordered mt-3">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Цена</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $s): ?>
            <tr>
                <td><?= (int)$s['id'] ?></td>
                <td><?= h($s['title']) ?></td>
                <td><?= number_format((float)$s['price'], 0, '', ' ') ?> ₽</td>
                <td>
                    <a href="edit_service.php?id=<?= (int)$s['id'] ?>" class="btn btn-warning btn-sm">✏️ Редактировать</a>
                    <form action="delete_service.php" method="POST" class="d-inline" onsubmit="return confirm('Удалить услугу?');">
                        <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Удалить</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($services) === 0): ?>
            <tr><td colspan="4" class="text-muted">Услуг пока нет. <a href="add_item.php">Добавить</a></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
