# SamurAI — Telegram-бот «ИИ-нумеролог» (MVP)

Этот репозиторий содержит MVP-скелет бота SamurAI согласно ТЗ v0.2. Проект пока использует заглушки по тарифам и промптам, но структура уже подготовлена для расширения.

## Быстрый старт
1. Скопируйте `config/config.example.php` в `config/config.php` и заполните значения.
2. Создайте базу данных SQLite:
   ```bash
   sqlite3 database/app.sqlite < database/schema.sql
   ```
3. Запустите PHP-сервер:
   ```bash
   php -S 0.0.0.0:8080 -t public
   ```
4. Подключите webhook Telegram на `/webhook`.

## Структура
- `public/index.php` — входная точка вебхука.
- `src/` — доменная логика (LLM, Telegram, репозитории).
- `database/schema.sql` — схема БД.
- `docs/TZ.md` — ТЗ v0.2.
- `docs/auto-deploy.md` — шаги автодеплоя.

## Примечание
Функциональность присутствует в виде каркаса. Для production нужно добавить реальные тексты тарифов, интеграции и UI-сообщения.
