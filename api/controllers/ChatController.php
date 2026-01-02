<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Utils.php';

final class ChatController
{
    public static function list(): void
    {
        Auth::requireUser();
        self::purgeOldMessages();
        $pdo = Database::connection();
        $stmt = $pdo->query("SELECT c.id, c.message, c.created_at, c.edited_at, c.deleted_at, u.name AS sender_name, u.role AS sender_role FROM chat_messages c JOIN users u ON c.sender_id = u.id WHERE c.created_at >= (NOW() - INTERVAL '8 days') ORDER BY c.created_at DESC");
        $messages = $stmt->fetchAll();
        $messageIds = array_map(static fn($row) => (int)$row['id'], $messages);

        $reactionsMap = [];
        if (!empty($messageIds)) {
            $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
            $stmt = $pdo->prepare('SELECT r.message_id, r.emoji, u.name AS user_name FROM chat_reactions r JOIN users u ON r.user_id = u.id WHERE r.message_id IN (' . $placeholders . ')');
            $stmt->execute($messageIds);
            foreach ($stmt->fetchAll() as $reaction) {
                $mid = (string)$reaction['message_id'];
                $emoji = $reaction['emoji'];
                $userName = $reaction['user_name'];
                if (!isset($reactionsMap[$mid])) {
                    $reactionsMap[$mid] = [];
                }
                if (!isset($reactionsMap[$mid][$emoji])) {
                    $reactionsMap[$mid][$emoji] = [];
                }
                $reactionsMap[$mid][$emoji][] = $userName;
            }
        }

        $payload = [];
        foreach ($messages as $row) {
            $mid = (string)$row['id'];
            $payload[] = [
                'id' => $row['id'],
                'message' => $row['deleted_at'] ? '[Message deleted]' : $row['message'],
                'created_at' => $row['created_at'],
                'edited_at' => $row['edited_at'],
                'deleted_at' => $row['deleted_at'],
                'sender_name' => $row['sender_name'],
                'sender_role' => $row['sender_role'],
                'reactions' => $reactionsMap[$mid] ?? new stdClass(),
            ];
        }

        Response::json(['success' => true, 'messages' => $payload]);
    }

    public static function create(): void
    {
        $user = Auth::requireUser();
        $data = Utils::jsonBody();
        $message = trim((string)($data['message'] ?? ''));
        if ($message === '') {
            Response::error('Message required', 422);
        }
        self::purgeOldMessages();
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO chat_messages (sender_id, message) VALUES (?, ?) RETURNING id, message, created_at, edited_at, deleted_at');
        $stmt->execute([(int)$user['id'], $message]);
        $row = $stmt->fetch();

        Response::json([
            'success' => true,
            'message' => [
                'id' => $row['id'],
                'message' => $row['message'],
                'created_at' => $row['created_at'],
                'edited_at' => $row['edited_at'],
                'deleted_at' => $row['deleted_at'],
                'sender_name' => $user['name'],
                'sender_role' => $user['role'],
                'reactions' => new stdClass(),
            ],
        ]);
    }

    public static function edit(int $messageId): void
    {
        $user = Auth::requireUser();
        $data = Utils::jsonBody();
        $message = trim((string)($data['message'] ?? ''));
        if ($message === '') {
            Response::error('Message required', 422);
        }
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT sender_id FROM chat_messages WHERE id = ?');
        $stmt->execute([$messageId]);
        $row = $stmt->fetch();
        if (!$row) {
            Response::error('Message not found', 404);
        }
        if ((int)$row['sender_id'] !== (int)$user['id'] && $user['role'] !== 'admin') {
            Response::error('Forbidden', 403);
        }
        $stmt = $pdo->prepare('UPDATE chat_messages SET message = ?, edited_at = NOW() WHERE id = ?');
        $stmt->execute([$message, $messageId]);
        Response::json(['success' => true]);
    }

    public static function delete(int $messageId): void
    {
        $user = Auth::requireUser();
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT sender_id FROM chat_messages WHERE id = ?');
        $stmt->execute([$messageId]);
        $row = $stmt->fetch();
        if (!$row) {
            Response::error('Message not found', 404);
        }
        if ((int)$row['sender_id'] !== (int)$user['id'] && $user['role'] !== 'admin') {
            Response::error('Forbidden', 403);
        }
        $stmt = $pdo->prepare('UPDATE chat_messages SET deleted_at = NOW(), deleted_by = ? WHERE id = ?');
        $stmt->execute([(int)$user['id'], $messageId]);
        Response::json(['success' => true]);
    }

    public static function react(int $messageId): void
    {
        $user = Auth::requireUser();
        $data = Utils::jsonBody();
        $emoji = trim((string)($data['emoji'] ?? ''));
        if ($emoji === '') {
            Response::error('Emoji required', 422);
        }
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id FROM chat_messages WHERE id = ?');
        $stmt->execute([$messageId]);
        if (!$stmt->fetch()) {
            Response::error('Message not found', 404);
        }

        $stmt = $pdo->prepare('SELECT id FROM chat_reactions WHERE message_id = ? AND user_id = ? AND emoji = ?');
        $stmt->execute([$messageId, (int)$user['id'], $emoji]);
        $existing = $stmt->fetch();
        if ($existing) {
            $stmt = $pdo->prepare('DELETE FROM chat_reactions WHERE id = ?');
            $stmt->execute([$existing['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO chat_reactions (message_id, user_id, emoji) VALUES (?, ?, ?)');
            $stmt->execute([$messageId, (int)$user['id'], $emoji]);
        }
        Response::json(['success' => true]);
    }

    private static function purgeOldMessages(): void
    {
        $pdo = Database::connection();
        $pdo->exec("DELETE FROM chat_messages WHERE created_at < (NOW() - INTERVAL '8 days')");
        $pdo->exec('DELETE FROM chat_reactions WHERE message_id NOT IN (SELECT id FROM chat_messages)');
    }
}
