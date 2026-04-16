<?php
declare(strict_types=1);

class Service
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function paginate(string $query, int $page, int $limit): array
    {
        $offset = max(0, ($page - 1) * $limit);
        $params = [];
        $where = '';
        if ($query !== '') {
            $where = ' WHERE title LIKE :query';
            $params['query'] = '%' . $query . '%';
        }

        $countStmt = $this->db->prepare('SELECT COUNT(*) FROM services' . $where);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = 'SELECT * FROM services' . $where . ' ORDER BY id DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
        ];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM services WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $service = $stmt->fetch();
        return $service ?: null;
    }

    public function create(string $title, string $description, float $price, ?string $imageUrl): bool
    {
        $stmt = $this->db->prepare('INSERT INTO services (title, description, price, image_url) VALUES (:title, :description, :price, :image_url)');
        return $stmt->execute([
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'image_url' => $imageUrl,
        ]);
    }

    public function update(int $id, string $title, string $description, float $price, ?string $imageUrl): bool
    {
        $stmt = $this->db->prepare('UPDATE services SET title = :title, description = :description, price = :price, image_url = :image_url WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'image_url' => $imageUrl,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM services WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
