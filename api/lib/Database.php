<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

final class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if (DB_URL !== '') {
            $url = parse_url(DB_URL);
            $host = $url['host'] ?? DB_HOST;
            $port = $url['port'] ?? DB_PORT;
            $user = $url['user'] ?? DB_USER;
            $pass = $url['pass'] ?? DB_PASS;
            $name = isset($url['path']) ? ltrim($url['path'], '/') : DB_NAME;
            $driver = DB_DRIVER ?: 'pgsql';
            $dsn = $driver . ':host=' . $host . ';port=' . $port . ';dbname=' . $name;
            if (!empty($url['query'])) {
                parse_str($url['query'], $params);
                if (!empty($params['sslmode'])) {
                    $dsn .= ';sslmode=' . $params['sslmode'];
                }
            }
            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return self::$instance;
        }

        if (DB_DRIVER === 'mysql') {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        } else {
            $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
        }

        self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return self::$instance;
    }
}
