<?php
require 'check_admin.php'; // Вызов охраны
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админка — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <div class="alert alert-success">
        <h1>Панель Администратора</h1>
        <p>Добро пожаловать в систему управления.</p>
        <a href="add_item.php" class="btn btn-success">+ Добавить услугу</a>
        <a href="admin_services.php" class="btn btn-primary">Редактировать / Удалить услуги</a>
        <a href="admin_orders.php" class="btn btn-info">Управление заказами</a>
        <a href="admin_seeder.php" class="btn btn-outline-secondary">Генератор данных (Seeder)</a>
        <a href="logout.php" class="btn btn-danger">Выйти</a>
    </div>
</body>
</html>
