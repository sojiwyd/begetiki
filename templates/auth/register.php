<?php $title = 'Регистрация'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">Регистрация</h1>
                <?php if ($message !== ''): ?>
                    <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= h($message) ?></div>
                <?php endif; ?>
                <?php if (!$success): ?>
                <form method="POST" action="<?= h(base_url('index.php?page=auth&action=register')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= h(Csrf::token()) ?>">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= h($old['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль ещё раз</label>
                        <input type="password" name="password_confirm" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Зарегистрироваться</button>
                </form>
                <?php else: ?>
                    <a href="<?= h(base_url('index.php?page=auth&action=login')) ?>" class="btn btn-primary w-100">Перейти ко входу</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
