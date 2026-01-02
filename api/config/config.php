<?php

declare(strict_types=1);

// Use environment variables (Render provides these)
// Falls back to default values for local development
if (!defined('DB_URL')) {
    define('DB_URL', getenv('DB_URL') ?: getenv('DATABASE_URL') ?: '');
}
if (!defined('DB_DRIVER')) {
    define('DB_DRIVER', getenv('DB_DRIVER') ?: 'pgsql');
}
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', getenv('DB_PORT') ?: '5432');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'bhs_union');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'bhs_user');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: '');
}
if (!defined('ALLOWED_ORIGINS')) {
    define('ALLOWED_ORIGINS', getenv('ALLOWED_ORIGINS') ?: '*');
}

// Token settings
const TOKEN_TTL_HOURS = 720; // 30 days

// Rate limiting
const RATE_LIMIT_MAX = 120; // requests
const RATE_LIMIT_WINDOW_SECONDS = 60; // per minute
