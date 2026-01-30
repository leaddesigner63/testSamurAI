<?php

final class MessageRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function log(int $userId, string $direction, string $messageType, ?string $text, ?array $payload = null): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO messages (user_id, direction, message_type, text, payload_json, created_at) VALUES (:user_id, :direction, :message_type, :text, :payload_json, :created_at)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'direction' => $direction,
            'message_type' => $messageType,
            'text' => $text,
            'payload_json' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }
}
