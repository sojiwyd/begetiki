<?php
declare(strict_types=1);

class AdminController
{
    private function handleImageUpload(): ?string
    {
        if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['image_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Ошибка загрузки файла.');
        }

        if ((int)$file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('Файл слишком большой. Максимум 5 МБ.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Разрешены только JPG, PNG, GIF, WEBP.');
        }

        $fileName = 'service_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
        $uploadDir = __DIR__ . '/../../public/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $fileName)) {
            throw new RuntimeException('Не удалось сохранить файл.');
        }

        return 'uploads/' . $fileName;
    }

    public function dashboard(): void
    {
        Auth::requireAdmin();

        $serviceModel = new Service();
        $orderModel = new Order();
        $serviceStats = $serviceModel->paginate('', 1, 1000);
        $orderStats = $orderModel->paginateForAdmin('', '', 1, 1000);

        $statusCounts = [
            'new' => 0,
            'processing' => 0,
            'done' => 0,
        ];

        foreach ($orderStats['items'] as $order) {
            $status = (string)($order['status'] ?? '');
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }

        View::render('admin/dashboard', [
            'serviceCount' => (int)$serviceStats['total'],
            'orderCount' => (int)$orderStats['total'],
            'statusCounts' => $statusCounts,
            'recentOrders' => array_slice($orderStats['items'], 0, 5),
        ]);
    }

    public function services(): void
    {
        Auth::requireAdmin();
        $serviceModel = new Service();
        $page = max(1, (int)($_GET['page_num'] ?? 1));
        $limit = 10;
        $query = trim((string)($_GET['q'] ?? ''));
        $result = $serviceModel->paginate($query, $page, $limit);
        $totalPages = max(1, (int)ceil($result['total'] / $limit));
        View::render('admin/services', [
            'services' => $result['items'],
            'page' => $page,
            'totalPages' => $totalPages,
            'query' => $query,
        ]);
    }

    public function createService(): void
    {
        Auth::requireAdmin();
        $message = '';
        $success = false;
        $form = ['title' => '', 'price' => '', 'description' => '', 'image_url' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::requireValid($_POST['csrf_token'] ?? null);
            $form['title'] = trim((string)($_POST['title'] ?? ''));
            $form['price'] = (string)($_POST['price'] ?? '');
            $form['description'] = trim((string)($_POST['description'] ?? ''));
            $form['image_url'] = trim((string)($_POST['image_url'] ?? ''));

            if ($form['title'] === '') {
                $message = 'Заполните название.';
            } else {
                try {
                    $uploadedImage = $this->handleImageUpload();
                    $imageUrl = $uploadedImage ?: ($form['image_url'] !== '' ? $form['image_url'] : null);
                    $serviceModel = new Service();
                    $serviceModel->create($form['title'], $form['description'], (float)$form['price'], $imageUrl);
                    $success = true;
                    $message = 'Услуга успешно добавлена.';
                    $form = ['title' => '', 'price' => '', 'description' => '', 'image_url' => ''];
                } catch (Throwable $e) {
                    $message = $e->getMessage();
                }
            }
        }

        View::render('admin/service_form', [
            'mode' => 'create',
            'form' => $form,
            'message' => $message,
            'success' => $success,
        ]);
    }

    public function editService(): void
    {
        Auth::requireAdmin();
        $serviceId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $serviceModel = new Service();
        $service = $serviceModel->find($serviceId);
        if (!$service) {
            http_response_code(404);
            exit('Услуга не найдена.');
        }

        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::requireValid($_POST['csrf_token'] ?? null);
            $title = trim((string)($_POST['title'] ?? ''));
            $price = (float)($_POST['price'] ?? 0);
            $description = trim((string)($_POST['description'] ?? ''));
            $imageUrl = trim((string)($_POST['image_url'] ?? ''));
            if ($title === '') {
                $message = 'Заполните название.';
            } else {
                try {
                    $uploadedImage = $this->handleImageUpload();
                    $serviceModel->update($serviceId, $title, $description, $price, $uploadedImage ?: ($imageUrl !== '' ? $imageUrl : null));
                    redirect(base_url('index.php?page=admin&action=services&updated=1'));
                } catch (Throwable $e) {
                    $message = $e->getMessage();
                }
            }
            $service = $serviceModel->find($serviceId) ?: $service;
        }

        View::render('admin/service_form', [
            'mode' => 'edit',
            'form' => $service,
            'message' => $message,
            'success' => false,
        ]);
    }

    public function deleteService(): void
    {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('index.php?page=admin&action=services'));
        }
        Csrf::requireValid($_POST['csrf_token'] ?? null);
        $serviceId = (int)($_POST['id'] ?? 0);
        if ($serviceId > 0) {
            $serviceModel = new Service();
            $serviceModel->delete($serviceId);
        }
        redirect(base_url('index.php?page=admin&action=services'));
    }

    public function orders(): void
    {
        Auth::requireAdmin();
        $orderModel = new Order();
        $page = max(1, (int)($_GET['page_num'] ?? 1));
        $limit = 10;
        $query = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $result = $orderModel->paginateForAdmin($query, $status, $page, $limit);
        $totalPages = max(1, (int)ceil($result['total'] / $limit));
        View::render('admin/orders', [
            'orders' => $result['items'],
            'page' => $page,
            'totalPages' => $totalPages,
            'query' => $query,
            'statusFilter' => $status,
            'allowedStatuses' => $orderModel->getAllowedStatuses(),
        ]);
    }

    public function updateOrder(): void
    {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('index.php?page=admin&action=orders'));
        }
        Csrf::requireValid($_POST['csrf_token'] ?? null);
        $orderId = (int)($_POST['order_id'] ?? 0);
        $action = (string)($_POST['action_type'] ?? '');
        $orderModel = new Order();
        if ($orderId > 0 && $action === 'update_status') {
            $status = (string)($_POST['status'] ?? 'new');
            if (in_array($status, $orderModel->getAllowedStatuses(), true)) {
                $orderModel->updateStatus($orderId, $status);
                redirect(base_url('index.php?page=admin&action=orders&updated=1'));
            }
        }
        if ($orderId > 0 && $action === 'delete_order') {
            $orderModel->delete($orderId);
            redirect(base_url('index.php?page=admin&action=orders&deleted=1'));
        }
        redirect(base_url('index.php?page=admin&action=orders'));
    }
}
