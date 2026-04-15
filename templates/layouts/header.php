<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title ?? 'Автосервис') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= h(base_url('index.php')) ?>">Автосервис</a>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= h(base_url('index.php?page=home&action=calendar')) ?>" class="btn btn-outline-light btn-sm">Календарь</a>
            <?php if (Auth::check()): ?>
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= h(base_url('index.php?page=admin&action=dashboard')) ?>" class="btn btn-danger btn-sm">Админка</a>
                <?php endif; ?>
                <a href="<?= h(base_url('index.php?page=profile&action=index')) ?>" class="btn btn-outline-light btn-sm">Мои заказы</a>
                <a href="<?= h(base_url('index.php?page=profile&action=changePassword')) ?>" class="btn btn-outline-light btn-sm">Сменить пароль</a>
                <a href="<?= h(base_url('index.php?page=auth&action=logout')) ?>" class="btn btn-light btn-sm">Выйти</a>
            <?php else: ?>
                <a href="<?= h(base_url('index.php?page=auth&action=login')) ?>" class="btn btn-primary btn-sm">Войти</a>
                <a href="<?= h(base_url('index.php?page=auth&action=register')) ?>" class="btn btn-outline-light btn-sm">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container pb-5">
