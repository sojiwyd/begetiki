<?php $title = 'Перенос записи'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Перенести запись #<?= (int)$orderId ?></h5></div>
            <div class="card-body">
                <p class="text-muted"><?= h($order['title']) ?> — <?= h(format_price($order['price'])) ?></p>
                <?php if ($message !== ''): ?><div class="alert alert-danger"><?= h($message) ?></div><?php endif; ?>
                <form method="POST" action="<?= h(base_url('index.php?page=orders&action=edit&id=' . (int)$orderId)) ?>">
                    <input type="hidden" name="id" value="<?= (int)$orderId ?>">
                    <input type="hidden" name="csrf_token" value="<?= h(Csrf::token()) ?>">
                    <div class="mb-3"><label class="form-label">Новая дата визита</label><input type="date" name="appointment_date" class="form-control" required value="<?= h($currentDate) ?>" min="<?= h($minDate) ?>"></div>
                    <div class="mb-3"><label class="form-label">Время визита</label><select class="form-select" name="appointment_time" required><?php foreach ($slots as $slot): ?><option value="<?= h($slot) ?>" <?= $slot === $currentTime ? 'selected' : '' ?>><?= h($slot) ?></option><?php endforeach; ?></select></div>
                    <button type="submit" class="btn btn-warning">Перенести</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
