<?php

final class Config
{
    private array $data;

    public function __construct(string $configPath)
    {
        if (!file_exists($configPath)) {
            throw new RuntimeException('Config file not found: ' . $configPath);
        }

        $config = require $configPath;
        if (!is_array($config)) {
            throw new RuntimeException('Config file must return array');
        }

        $this->data = $config;
        date_default_timezone_set($this->getString('APP_TIMEZONE', 'Europe/Moscow'));
    }

    public function getString(string $key, string $default = ''): string
    {
        $value = $this->data[$key] ?? $default;
        return is_string($value) ? $value : $default;
    }

    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->data[$key] ?? $default;
        return is_numeric($value) ? (int) $value : $default;
    }

    public function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->data[$key] ?? $default;
        return is_numeric($value) ? (float) $value : $default;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->data[$key] ?? $default;
        return is_bool($value) ? $value : $default;
    }
}
