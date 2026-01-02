<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Utils.php';

final class MessagesController
{
    public static function list(): void
    {
        Auth::requireRole('admin');
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT m.id, m.subject, m.body, m.created_at, u.name AS sender_name FROM messages m JOIN users u ON m.sender_id = u.id ORDER BY m.created_at DESC');
        Response::json(['success' => true, 'messages' => $stmt->fetchAll()]);
    }
}

