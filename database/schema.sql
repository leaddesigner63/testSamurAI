-- SQLite schema for SamurAI MVP
PRAGMA journal_mode = WAL;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tg_id TEXT NOT NULL UNIQUE,
    username TEXT,
    first_name TEXT,
    last_name TEXT,
    created_at TEXT NOT NULL,
    last_seen_at TEXT NOT NULL,
    last_tariff_selected INTEGER,
    is_bought INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS user_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    birth_date TEXT NOT NULL,
    birth_time TEXT,
    birth_name TEXT NOT NULL,
    birth_place TEXT NOT NULL,
    created_at TEXT NOT NULL,
    is_current INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS purchases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    tariff_id INTEGER NOT NULL,
    amount INTEGER NOT NULL,
    currency TEXT NOT NULL DEFAULT 'RUB',
    status TEXT NOT NULL,
    provider TEXT NOT NULL,
    provider_payment_id TEXT,
    created_at TEXT NOT NULL,
    paid_at TEXT,
    comment TEXT,
    meta_json TEXT,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    direction TEXT NOT NULL,
    message_type TEXT NOT NULL,
    text TEXT,
    payload_json TEXT,
    created_at TEXT NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    tariff_id INTEGER NOT NULL,
    profile_id INTEGER NOT NULL,
    report_text TEXT NOT NULL,
    report_json TEXT,
    llm_provider TEXT NOT NULL,
    llm_model TEXT NOT NULL,
    created_at TEXT NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(profile_id) REFERENCES user_profiles(id)
);

CREATE TABLE IF NOT EXISTS report_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    is_followup_open INTEGER NOT NULL DEFAULT 1,
    followup_count INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    closed_at TEXT,
    FOREIGN KEY(report_id) REFERENCES reports(id),
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tg_id TEXT NOT NULL UNIQUE,
    username TEXT,
    created_at TEXT NOT NULL,
    added_by TEXT
);

CREATE TABLE IF NOT EXISTS broadcasts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_by_tg_id TEXT NOT NULL,
    segment TEXT NOT NULL,
    text TEXT NOT NULL,
    status TEXT NOT NULL,
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS broadcast_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    broadcast_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    status TEXT NOT NULL,
    error TEXT,
    sent_at TEXT NOT NULL,
    FOREIGN KEY(broadcast_id) REFERENCES broadcasts(id),
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS tariff_policies (
    tariff_id INTEGER PRIMARY KEY,
    title TEXT NOT NULL,
    system_prompt_report TEXT NOT NULL,
    user_prompt_template_report TEXT NOT NULL,
    system_prompt_followup TEXT NOT NULL,
    followup_limit INTEGER NOT NULL,
    followup_window_hours INTEGER,
    followup_rules TEXT NOT NULL,
    output_format TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS llm_call_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    report_id INTEGER,
    session_id INTEGER,
    purpose TEXT NOT NULL,
    provider TEXT NOT NULL,
    model TEXT NOT NULL,
    request_id TEXT,
    latency_ms INTEGER NOT NULL,
    ok INTEGER NOT NULL,
    error_text TEXT,
    prompt_tokens INTEGER,
    output_tokens INTEGER,
    total_tokens INTEGER,
    created_at TEXT NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(report_id) REFERENCES reports(id),
    FOREIGN KEY(session_id) REFERENCES report_sessions(id)
);
