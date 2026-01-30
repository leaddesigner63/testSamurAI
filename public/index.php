<?php

require __DIR__ . '/../src/Config.php';
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/LLM/LlmResult.php';
require __DIR__ . '/../src/LLM/LlmProviderInterface.php';
require __DIR__ . '/../src/LLM/OpenAiProvider.php';
require __DIR__ . '/../src/LLM/GeminiProvider.php';
require __DIR__ . '/../src/LLM/LlmService.php';
require __DIR__ . '/../src/Repositories/UserRepository.php';
require __DIR__ . '/../src/Repositories/MessageRepository.php';
require __DIR__ . '/../src/Repositories/TariffPolicyRepository.php';
require __DIR__ . '/../src/Telegram/TelegramClient.php';
require __DIR__ . '/../src/Services/BotService.php';

$config = new Config(__DIR__ . '/../config/config.php');
$db = new Database($config->getString('DATABASE_PATH'));
$pdo = $db->pdo();

$telegram = new TelegramClient($config->getString('TELEGRAM_BOT_TOKEN'));
$users = new UserRepository($pdo);
$messages = new MessageRepository($pdo);

$bot = new BotService($telegram, $users, $messages, $pdo);

$payload = file_get_contents('php://input');
$update = json_decode($payload, true);

if (!is_array($update)) {
    http_response_code(200);
    echo 'OK';
    exit;
}

$bot->handleUpdate($update);
http_response_code(200);
echo 'OK';
