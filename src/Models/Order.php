<?php
declare(strict_types=1);

class Order
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllowedStatuses(): array
    {
        return ['new', 'processing', 'done'];
    }

    public function create(int $userId, int $serviceId, string $appointmentDate, ?string $damagePhotoUrl): bool
    {
        $stmt = $this->db->prepare('INSERT INTO orders (user_id, service_id, appointment_date, damage_photo_url) VALUES (:user_id, :service_id, :appointment_date, :damage_photo_url)');
        return $stmt->execute([
            'user_id' => $userId,
            'service_id' => $serviceId,
            'appointment_date' => $appointmentDate,
            'damage_photo_url' => $damagePhotoUrl,
        ]);
    }

    public function findRecentDuplicate(int $userId, int $serviceId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM orders WHERE user_id = :user_id AND service_id = :service_id AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE) LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'service_id' => $serviceId]);
        return (bool)$stmt->fetch();
    }

    public function isSlotBusy(string $appointmentDate, ?int $excludeOrderId = null): bool
    {
        $sql = 'SELECT id FROM orders WHERE appointment_date = :appointment_date AND status IN (\'new\', \'processing\')';
        $params = ['appointment_date' => $appointmentDate];
        if ($excludeOrderId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeOrderId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT orders.id AS order_id, orders.created_at, orders.appointment_date, orders.damage_photo_url, orders.status,
                    services.title, services.price, services.image_url
             FROM orders
             JOIN services ON orders.service_id = services.id
             WHERE orders.user_id = :user_id
             ORDER BY orders.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function findUserOrderDetails(int $orderId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT orders.*, services.title, services.price, services.description, services.image_url
             FROM orders
             JOIN services ON orders.service_id = services.id
             WHERE orders.id = :id AND orders.user_id = :user_id LIMIT 1'
        );
        $stmt->execute(['id' => $orderId, 'user_id' => $userId]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public function findUserOrderForEdit(int $orderId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT orders.*, services.title, services.price
             FROM orders
             JOIN services ON orders.service_id = services.id
             WHERE orders.id = :id AND orders.user_id = :user_id LIMIT 1'
        );
        $stmt->execute(['id' => $orderId, 'user_id' => $userId]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public function updateAppointment(int $orderId, int $userId, string $appointmentDate): bool
    {
        $stmt = $this->db->prepare('UPDATE orders SET appointment_date = :appointment_date WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['appointment_date' => $appointmentDate, 'id' => $orderId, 'user_id' => $userId]);
    }

    public function cancelEligible(int $orderId, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM orders WHERE id = :id AND user_id = :user_id AND status = 'new' AND appointment_date > DATE_ADD(NOW(), INTERVAL 24 HOUR)");
        $stmt->execute(['id' => $orderId, 'user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function paginateForAdmin(string $query, string $statusFilter, int $page, int $limit): array
    {
        $offset = max(0, ($page - 1) * $limit);
        $where = [];
        $params = [];
        if ($query !== '') {
            $where[] = '(users.email LIKE :query OR services.title LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }
        if ($statusFilter !== '' && in_array($statusFilter, $this->getAllowedStatuses(), true)) {
            $where[] = 'orders.status = :status';
            $params['status'] = $statusFilter;
        }
        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

        $countSql = 'SELECT COUNT(*) FROM orders JOIN users ON orders.user_id = users.id JOIN services ON orders.service_id = services.id' . $whereSql;
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = 'SELECT orders.id AS order_id, orders.created_at, orders.appointment_date, orders.damage_photo_url, orders.status,
                       users.email, services.title, services.price
                FROM orders
                JOIN users ON orders.user_id = users.id
                JOIN services ON orders.service_id = services.id' . $whereSql . '
                ORDER BY orders.id DESC
                LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE orders SET status = :status WHERE id = :id');
        return $stmt->execute(['status' => $status, 'id' => $orderId]);
    }

    public function delete(int $orderId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM orders WHERE id = :id');
        return $stmt->execute(['id' => $orderId]);
    }

    public function getBusySlots(string $date): array
    {
        $dayStart = $date . ' 09:00:00';
        $dayEnd = $date . ' 18:00:00';

        $stmt = $this->db->prepare(
            "SELECT DATE_FORMAT(appointment_date, '%H:%i') AS slot
             FROM orders
             WHERE appointment_date >= :start
               AND appointment_date < :end
               AND status IN ('new', 'processing')
               AND appointment_date IS NOT NULL"
        );
        $stmt->execute(['start' => $dayStart, 'end' => $dayEnd]);

        $busy = [];
        while ($row = $stmt->fetch()) {
            $busy[$row['slot']] = true;
        }

        $slots = [];
        for ($h = 9; $h < 18; $h++) {
            foreach ([0, 30] as $m) {
                $slot = sprintf('%02d:%02d', $h, $m);
                $slots[] = [
                    'time' => $slot,
                    'is_busy' => !empty($busy[$slot]),
                ];
            }
        }

        return $slots;
    }
}
