<?php
declare(strict_types=1);

class OrderController
{
    private function getSlots(): array
    {
        $slots = [];
        for ($h = 9; $h < 18; $h++) {
            $slots[] = sprintf('%02d:00', $h);
            if ($h < 17) {
                $slots[] = sprintf('%02d:30', $h);
            }
        }
        return $slots;
    }

    private function handleUpload(string $fieldName, string $prefix, array $allowedMimes): ?string
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES[$fieldName];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Ошибка загрузки файла.');
        }

        if ((int)$file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('Файл слишком большой. Максимум 5 МБ.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!isset($allowedMimes[$mime])) {
            throw new RuntimeException('Недопустимый тип файла.');
        }

        $fileName = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mime];
        $uploadDir = __DIR__ . '/../../public/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Не удалось сохранить файл.');
        }

        return 'uploads/' . $fileName;
    }

    public function create(): void
    {
        Auth::requireLogin();

        $serviceId = (int)($_GET['service_id'] ?? $_POST['service_id'] ?? 0);
        $serviceModel = new Service();
        $service = $serviceModel->find($serviceId);
        if ($serviceId <= 0 || !$service) {
            http_response_code(404);
            exit('Услуга не найдена.');
        }

        $message = '';
        $success = false;
        $slots = $this->getSlots();
        $minDate = (new DateTime())->format('Y-m-d');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::requireValid($_POST['csrf_token'] ?? null);
            $appointmentDate = trim((string)($_POST['appointment_date'] ?? ''));
            $appointmentTime = trim((string)($_POST['appointment_time'] ?? ''));

            if ($appointmentDate === '' || $appointmentTime === '' || !in_array($appointmentTime, $slots, true)) {
                $message = 'Укажите дату и время визита.';
            } else {
                $appointment = $appointmentDate . ' ' . $appointmentTime . ':00';
                $dt = DateTime::createFromFormat('Y-m-d H:i:s', $appointment);
                if (!$dt || $dt <= new DateTime()) {
                    $message = 'Укажите будущую дату и время.';
                } else {
                    $orderModel = new Order();
                    if ($orderModel->isSlotBusy($appointment)) {
                        $message = 'Этот слот уже занят. Выберите другое время.';
                    } elseif ($orderModel->findRecentDuplicate((int)Auth::id(), $serviceId)) {
                        $message = 'Вы уже записывались на эту услугу недавно. Подождите 5 минут.';
                    } else {
                        try {
                            $damagePhoto = $this->handleUpload('damage_photo', 'damage', [
                                'image/jpeg' => 'jpg',
                                'image/png' => 'png',
                                'image/gif' => 'gif',
                                'image/webp' => 'webp',
                            ]);
                            $orderModel->create((int)Auth::id(), $serviceId, $appointment, $damagePhoto);
                            $success = true;
                            $message = 'Запись успешно оформлена на ' . $dt->format('d.m.Y H:i') . '.';
                        } catch (Throwable $e) {
                            $message = $e->getMessage();
                        }
                    }
                }
            }
        }

        View::render('orders/create', [
            'service' => $service,
            'serviceId' => $serviceId,
            'slots' => $slots,
            'minDate' => $minDate,
            'message' => $message,
            'success' => $success,
        ]);
    }

    public function show(): void
    {
        Auth::requireLogin();
        $orderId = (int)($_GET['id'] ?? 0);
        $orderModel = new Order();
        $order = $orderModel->findUserOrderDetails($orderId, (int)Auth::id());
        if (!$order) {
            http_response_code(404);
            exit('Заказ не найден или у вас нет прав на его просмотр.');
        }
        View::render('orders/show', ['order' => $order]);
    }

    public function edit(): void
    {
        Auth::requireLogin();
        $orderId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $orderModel = new Order();
        $order = $orderModel->findUserOrderForEdit($orderId, (int)Auth::id());
        if (!$order) {
            http_response_code(404);
            exit('Заказ не найден.');
        }
        if (($order['status'] ?? '') !== 'new' || empty($order['appointment_date'])) {
            http_response_code(400);
            exit('Эту запись нельзя перенести.');
        }

        $slots = $this->getSlots();
        $currentDate = date('Y-m-d', strtotime((string)$order['appointment_date']));
        $currentTime = date('H:i', strtotime((string)$order['appointment_date']));
        if (!in_array($currentTime, $slots, true)) {
            $currentTime = '09:00';
        }

        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::requireValid($_POST['csrf_token'] ?? null);
            $currentDate = trim((string)($_POST['appointment_date'] ?? $currentDate));
            $currentTime = trim((string)($_POST['appointment_time'] ?? $currentTime));
            if ($currentDate === '' || $currentTime === '' || !in_array($currentTime, $slots, true)) {
                $message = 'Укажите дату и время.';
            } else {
                $appointment = $currentDate . ' ' . $currentTime . ':00';
                $dt = DateTime::createFromFormat('Y-m-d H:i:s', $appointment);
                if (!$dt || $dt <= new DateTime()) {
                    $message = 'Укажите будущую дату и время.';
                } elseif ($orderModel->isSlotBusy($appointment, $orderId)) {
                    $message = 'Этот слот уже занят. Выберите другое время.';
                } else {
                    $orderModel->updateAppointment($orderId, (int)Auth::id(), $appointment);
                    redirect(base_url('index.php?page=profile&action=index&updated=1'));
                }
            }
        }

        View::render('orders/edit', [
            'order' => $order,
            'orderId' => $orderId,
            'slots' => $slots,
            'currentDate' => $currentDate,
            'currentTime' => $currentTime,
            'minDate' => (new DateTime())->format('Y-m-d'),
            'message' => $message,
        ]);
    }

    public function cancel(): void
    {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('index.php?page=profile&action=index'));
        }
        Csrf::requireValid($_POST['csrf_token'] ?? null);
        $orderId = (int)($_POST['id'] ?? 0);
        $orderModel = new Order();
        $success = $orderModel->cancelEligible($orderId, (int)Auth::id());
        View::render('orders/cancel', ['success' => $success]);
    }
}
