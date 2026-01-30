<?php

final class TelegramClient
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function sendMessage(int $chatId, string $text, array $replyMarkup = []): void
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => $replyMarkup ? json_encode($replyMarkup, JSON_UNESCAPED_UNICODE) : null,
        ];

        $this->post('sendMessage', array_filter($payload, fn($value) => $value !== null));
    }

    public function sendDocument(int $chatId, string $filePath, string $caption = ''): void
    {
        $payload = [
            'chat_id' => $chatId,
            'caption' => $caption,
            'document' => new CURLFile($filePath),
        ];

        $this->post('sendDocument', $payload, true);
    }

    private function post(string $method, array $payload, bool $multipart = false): void
    {
        $url = 'https://api.telegram.org/bot' . $this->token . '/' . $method;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $multipart ? [] : ['Content-Type: application/x-www-form-urlencoded']);

        $result = curl_exec($ch);
        if ($result === false) {
            throw new RuntimeException('Telegram API error: ' . curl_error($ch));
        }
    }
}
