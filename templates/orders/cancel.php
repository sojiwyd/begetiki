<?php $title = 'Отмена записи'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm text-center">
            <div class="card-body p-4">
                <?php if ($success): ?>
                    <h5 class="card-title text-success">Запись успешно отменена</h5>
                    <a href="<?= h(base_url('index.php?page=profile&action=index')) ?>" class="btn btn-primary mt-2">В мои заказы</a>
                <?php else: ?>
                    <h5 class="card-title text-danger">Не удалось отменить запись</h5>
                    <p class="text-muted small">Либо запись не ваша, либо осталось меньше 24 часов до визита, либо дата уже прошла.</p>
                    <a href="<?= h(base_url('index.php?page=profile&action=index')) ?>" class="btn btn-outline-secondary mt-2">Вернуться</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
