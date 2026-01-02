<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/../config/config.php';

final class RateLimiter
{
    public static function enforce(string $scope = 'global', int $max = RATE_LIMIT_MAX): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = $scope . ':' . $ip;
        $windowStart = (new DateTimeImmutable())->modify('-' . RATE_LIMIT_WINDOW_SECONDS . ' seconds');

        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id, request_count, window_start FROM rate_limits WHERE rate_key = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();

        if (!$row) {
            $stmt = $pdo->prepare('INSERT INTO rate_limits (rate_key, window_start, request_count) VALUES (?, NOW(), 1)');
            $stmt->execute([$key]);
            return;
        }

        $currentWindow = new DateTimeImmutable($row['window_start']);
        if ($currentWindow < $windowStart) {
            $stmt = $pdo->prepare('UPDATE rate_limits SET window_start = NOW(), request_count = 1 WHERE id = ?');
            $stmt->execute([(int)$row['id']]);
            return;
        }

        $count = (int)$row['request_count'] + 1;
        if ($count > $max) {
            Response::error('Rate limit exceeded', 429);
        }

        $stmt = $pdo->prepare('UPDATE rate_limits SET request_count = ? WHERE id = ?');
        $stmt->execute([$count, (int)$row['id']]);
    }
}
