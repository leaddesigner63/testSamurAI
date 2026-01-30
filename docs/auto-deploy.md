# Автодеплой SamurAI (шаги для настройки)

Ниже — рекомендованный минимальный автодеплой через GitHub Actions и SSH на ваш сервер.

## 1. Подготовка сервера
1. Создайте пользователя, например `samurai`, и добавьте его в `sudo`.
2. Установите PHP 8.2+, SQLite и nginx (или caddy).
3. Создайте каталог приложения:
   ```bash
   sudo mkdir -p /var/www/samurai
   sudo chown samurai:samurai /var/www/samurai
   ```
4. Скопируйте `config/config.example.php` в `config/config.php` и заполните секреты на сервере.

## 2. SSH-ключ для деплоя
1. Сгенерируйте ключ:
   ```bash
   ssh-keygen -t ed25519 -C "samurai-deploy"
   ```
2. Добавьте публичный ключ в `~/.ssh/authorized_keys` пользователя `samurai`.

## 3. Secrets в GitHub
В настройках репозитория добавьте secrets:
- `DEPLOY_HOST` — IP/домен сервера
- `DEPLOY_USER` — пользователь (например `samurai`)
- `DEPLOY_SSH_KEY` — приватный ключ
- `DEPLOY_PATH` — путь `/var/www/samurai`

## 4. Workflow
Файл `.github/workflows/deploy.yml` уже настроен на деплой при пуше в `main`.

## 5. Проверка
1. Сделайте коммит в `main`.
2. Проверьте статус GitHub Actions.
3. Убедитесь, что `/webhook` отвечает на запрос.

## 6. Rollback (быстро)
1. На сервере выполните:
   ```bash
   cd /var/www/samurai
   git log --oneline
   git checkout <commit>
   ```
2. Перезапустите PHP-FPM/сервис (если используется systemd).
