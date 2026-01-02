<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/../config/config.php';

final class Auth
{
    public static function tryUser(): ?array
    {
        $token = self::bearerToken();
        if ($token === null) {
            return null;
        }
        $hash = hash('sha256', $token);
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT u.* FROM session_tokens t JOIN users u ON u.id = t.user_id WHERE t.token_hash = ? AND t.expires_at > NOW()');
        $stmt->execute([$hash]);
        $user = $stmt->fetch();
        if (!$user) {
            return null;
        }
        if ($user['status'] !== 'active') {
            return null;
        }
        return $user;
    }

    public static function requireUser(): array
    {
        $token = self::bearerToken();
        if ($token === null) {
            Response::error('Unauthorized', 401);
        }
        $hash = hash('sha256', $token);
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT u.* FROM session_tokens t JOIN users u ON u.id = t.user_id WHERE t.token_hash = ? AND t.expires_at > NOW()');
        $stmt->execute([$hash]);
        $user = $stmt->fetch();
        if (!$user) {
            Response::error('Unauthorized', 401);
        }
        if ($user['status'] !== 'active') {
            Response::error('Account not active', 403);
        }
        return $user;
    }

    public static function requireRole(string $role): array
    {
        $user = self::requireUser();
        if ($user['role'] !== $role) {
            Response::error('Forbidden', 403);
        }
        return $user;
    }

    public static function issueToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $expires = (new DateTimeImmutable())->modify('+' . TOKEN_TTL_HOURS . ' hours');

        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO session_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $hash, $expires->format('Y-m-d H:i:s')]);

        return $token;
    }

    public static function revokeToken(string $token): void
    {
        $hash = hash('sha256', $token);
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM session_tokens WHERE token_hash = ?');
        $stmt->execute([$hash]);
    }

    private static function bearerToken(): ?string
    {
        // Try multiple methods to get Authorization header
        $header = '';
        
        // Method 1: Standard HTTP_AUTHORIZATION (works with most servers)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['HTTP_AUTHORIZATION'];
        }
        // Method 2: Alternative Authorization key
        elseif (isset($_SERVER['Authorization'])) {
            $header = $_SERVER['Authorization'];
        }
        // Method 3: Apache request headers (if function exists)
        elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
        // Method 4: Check REDIRECT_HTTP_AUTHORIZATION (some Apache configs)
        elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        
        if ($header && stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }
        
        return null;
    }
}
