<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Utils.php';
require_once __DIR__ . '/../lib/Auth.php';

final class ClientLogsController
{
    public static function create(): void
    {
        $data = Utils::jsonBody();
        $level = trim((string)($data['level'] ?? 'error'));
        $message = trim((string)($data['message'] ?? ''));
        $stack = (string)($data['stack'] ?? '');
        $platform = trim((string)($data['platform'] ?? ''));
        $context = trim((string)($data['context'] ?? ''));

        if ($message === '') {
            Response::error('Message required', 422);
        }

        $user = Auth::tryUser();
        $userId = $user ? (int)$user['id'] : null;

        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO client_logs (user_id, level, message, stack, platform, context) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $level, $message, $stack, $platform, $context]);

        Response::json(['success' => true]);
    }
}
