<?php
declare(strict_types=1);

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(string $email, string $password, string $role = 'client'): bool
    {
        $stmt = $this->db->prepare('INSERT INTO users (email, password_hash, role) VALUES (:email, :hash, :role)');
        return $stmt->execute([
            'email' => $email,
            'hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
    }

    public function changePassword(int $userId, string $newPassword): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        return $stmt->execute([
            'hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $userId,
        ]);
    }
}
