<?php $title = $mode === 'create' ? 'Новая услуга' : 'Редактировать услугу'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="<?= h(base_url('index.php?page=admin&action=services')) ?>" class="btn btn-secondary">← Назад</a>
</div>
<?php if ($message !== ''): ?><div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= h($message) ?></div><?php endif; ?>
<form method="POST" enctype="multipart/form-data" class="card shadow-sm p-4" action="<?= h($mode === 'create' ? base_url('index.php?page=admin&action=createService') : base_url('index.php?page=admin&action=editService&id=' . (int)$form['id'])) ?>">
    <input type="hidden" name="csrf_token" value="<?= h(Csrf::token()) ?>">
    <?php if ($mode === 'edit'): ?><input type="hidden" name="id" value="<?= (int)$form['id'] ?>"><?php endif; ?>
    <div class="mb-3"><label class="form-label">Название</label><input type="text" name="title" class="form-control" value="<?= h($form['title'] ?? '') ?>" required></div>
    <div class="mb-3"><label class="form-label">Цена</label><input type="number" name="price" class="form-control" value="<?= h((string)($form['price'] ?? '')) ?>" step="0.01"></div>
    <div class="mb-3"><label class="form-label">Описание</label><textarea name="description" class="form-control" rows="4"><?= h($form['description'] ?? '') ?></textarea></div>
    <div class="mb-3"><label class="form-label">URL картинки</label><input type="text" name="image_url" class="form-control" value="<?= h($form['image_url'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label">Или загрузите файл</label><input type="file" name="image_file" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp"></div>
    <button type="submit" class="btn btn-<?= $mode === 'create' ? 'success' : 'warning' ?>"><?= $mode === 'create' ? 'Сохранить' : 'Обновить' ?></button>
</form>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
