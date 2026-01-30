<?php

return [
    'APP_ENV' => 'local',
    'APP_TIMEZONE' => 'Europe/Moscow',
    'TELEGRAM_BOT_TOKEN' => 'set-me',
    'TELEGRAM_WEBHOOK_SECRET' => 'set-me',
    'DATABASE_PATH' => __DIR__ . '/../database/app.sqlite',

    'LLM_PROVIDER' => 'openai',
    'OPENAI_API_KEY' => 'set-me',
    'OPENAI_MODEL_REPORT' => 'gpt-4o-mini',
    'OPENAI_MODEL_FOLLOWUP' => 'gpt-4o-mini',
    'GEMINI_API_KEY' => 'set-me',
    'GEMINI_MODEL_REPORT' => 'gemini-1.5-flash',
    'GEMINI_MODEL_FOLLOWUP' => 'gemini-1.5-flash',
    'LLM_TEMPERATURE' => 0.7,
    'LLM_MAX_OUTPUT_TOKENS' => 1200,
    'LLM_TIMEOUT_SECONDS' => 30,
    'LLM_FALLBACK_ENABLED' => false,
];
