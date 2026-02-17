<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h1 class="h4 mb-4">Регистрация</h1>
                    <?php if ($message !== ''): ?>
                        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
                            <?= $success ? $message : h($message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$success): ?>
                    <form method="POST" action="register.php">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                required
                                value="<?= h($old['email'] ?? '') ?>"
                            >
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль еще раз</label>
                            <input type="password" name="password_confirm" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Зарегистрироваться</button>
                    </form>
                    <?php endif; ?>

                    <p class="mt-3 mb-0 text-center">
                        <a href="login.php">Войти</a> · <a href="index.php">На главную</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
