<?php

final class BotService
{
    private TelegramClient $telegram;
    private UserRepository $users;
    private MessageRepository $messages;
    private PDO $pdo;

    public function __construct(
        TelegramClient $telegram,
        UserRepository $users,
        MessageRepository $messages,
        PDO $pdo
    ) {
        $this->telegram = $telegram;
        $this->users = $users;
        $this->messages = $messages;
        $this->pdo = $pdo;
    }

    public function handleUpdate(array $update): void
    {
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
            return;
        }

        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        }
    }

    private function handleMessage(array $message): void
    {
        $user = $this->users->upsertUser($message['from']);
        $text = trim($message['text'] ?? '');

        $this->messages->log((int) $user['id'], 'in', 'text', $text);

        if ($text === '/start') {
            $this->sendWelcome((int) $user['id'], (int) $message['chat']['id']);
            return;
        }

        $this->telegram->sendMessage((int) $message['chat']['id'], 'Сообщение получено. Анкета и follow-up в разработке.');
        $this->messages->log((int) $user['id'], 'out', 'system_event', 'Сообщение получено. Анкета и follow-up в разработке.');
    }

    private function handleCallback(array $callback): void
    {
        $user = $this->users->upsertUser($callback['from']);
        $data = $callback['data'] ?? '';

        $this->messages->log((int) $user['id'], 'in', 'system_event', null, ['callback' => $data]);

        if (str_starts_with($data, 'tariff:')) {
            $tariff = (int) str_replace('tariff:', '', $data);
            $this->setUserTariff((int) $user['id'], $tariff);
            $this->telegram->sendMessage((int) $callback['message']['chat']['id'], 'Вы выбрали тариф ' . $tariff . '. Введите дату рождения (ДД.ММ.ГГГГ).');
            $this->messages->log((int) $user['id'], 'out', 'system_event', 'Запуск анкеты по тарифу ' . $tariff);
            return;
        }

        $this->telegram->sendMessage((int) $callback['message']['chat']['id'], 'Команда пока не поддерживается.');
        $this->messages->log((int) $user['id'], 'out', 'system_event', 'Команда пока не поддерживается.');
    }

    private function sendWelcome(int $userId, int $chatId): void
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Тариф 0', 'callback_data' => 'tariff:0'],
                    ['text' => '560', 'callback_data' => 'tariff:560'],
                ],
                [
                    ['text' => '2190', 'callback_data' => 'tariff:2190'],
                    ['text' => '5930', 'callback_data' => 'tariff:5930'],
                ],
            ],
        ];

        $text = 'Добро пожаловать в SamurAI! Выберите тариф:';
        $this->telegram->sendMessage($chatId, $text, $keyboard);
        $this->messages->log($userId, 'out', 'system_event', $text);
    }

    private function setUserTariff(int $userId, int $tariff): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_tariff_selected = :tariff WHERE id = :id');
        $stmt->execute(['tariff' => $tariff, 'id' => $userId]);
    }
}
