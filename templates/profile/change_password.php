<?php $title = 'Смена пароля'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">Смена пароля</h1>
                <?php if ($message !== ''): ?>
                    <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= h($message) ?></div>
                <?php endif; ?>
                <?php if (!$success): ?>
                <form method="POST" action="<?= h(base_url('index.php?page=profile&action=changePassword')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= h(Csrf::token()) ?>">
                    <div class="mb-3"><label class="form-label">Текущий пароль</label><input type="password" name="old_password" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Новый пароль</label><input type="password" name="new_password" class="form-control" required minlength="8"></div>
                    <div class="mb-3"><label class="form-label">Повторите новый пароль</label><input type="password" name="new_password2" class="form-control" required></div>
                    <button type="submit" class="btn btn-primary w-100">Сохранить</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
