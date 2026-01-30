<?php

final class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function upsertUser(array $telegramUser): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE tg_id = :tg_id');
        $stmt->execute(['tg_id' => (string) $telegramUser['id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        $now = gmdate('Y-m-d H:i:s');
        if ($existing) {
            $update = $this->pdo->prepare(
                'UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, last_seen_at = :last_seen_at WHERE tg_id = :tg_id'
            );
            $update->execute([
                'username' => $telegramUser['username'] ?? null,
                'first_name' => $telegramUser['first_name'] ?? null,
                'last_name' => $telegramUser['last_name'] ?? null,
                'last_seen_at' => $now,
                'tg_id' => (string) $telegramUser['id'],
            ]);

            return array_merge($existing, [
                'username' => $telegramUser['username'] ?? null,
                'first_name' => $telegramUser['first_name'] ?? null,
                'last_name' => $telegramUser['last_name'] ?? null,
                'last_seen_at' => $now,
            ]);
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO users (tg_id, username, first_name, last_name, created_at, last_seen_at) VALUES (:tg_id, :username, :first_name, :last_name, :created_at, :last_seen_at)'
        );
        $insert->execute([
            'tg_id' => (string) $telegramUser['id'],
            'username' => $telegramUser['username'] ?? null,
            'first_name' => $telegramUser['first_name'] ?? null,
            'last_name' => $telegramUser['last_name'] ?? null,
            'created_at' => $now,
            'last_seen_at' => $now,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        return $this->findById($id);
    }

    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
