<?php $title = 'Запись на услугу'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Записаться на <?= h($service['title']) ?></h5></div>
            <div class="card-body">
                <p class="text-muted"><?= h(format_price($service['price'])) ?></p>
                <?php if ($message !== ''): ?><div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= h($message) ?></div><?php endif; ?>
                <?php if (!$success): ?>
                <form method="POST" action="<?= h(base_url('index.php?page=orders&action=create')) ?>" enctype="multipart/form-data">
                    <input type="hidden" name="service_id" value="<?= (int)$serviceId ?>">
                    <input type="hidden" name="csrf_token" value="<?= h(Csrf::token()) ?>">
                    <div class="mb-3"><label class="form-label">Дата визита</label><input type="date" name="appointment_date" class="form-control" required min="<?= h($minDate) ?>"></div>
                    <div class="mb-3"><label class="form-label">Время визита</label><select class="form-select" name="appointment_time" required><?php foreach ($slots as $slot): ?><option value="<?= h($slot) ?>"><?= h($slot) ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label">Фото повреждения</label><input type="file" name="damage_photo" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp"></div>
                    <button type="submit" class="btn btn-primary">Записаться</button>
                </form>
                <?php else: ?>
                    <a href="<?= h(base_url('index.php?page=profile&action=index')) ?>" class="btn btn-primary">Перейти в мои заказы</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
