<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Utils.php';

final class AnnouncementsController
{
    public static function list(): void
    {
        Auth::requireUser();
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT a.id, a.title, a.content, a.published, a.created_at, u.name AS author FROM announcements a JOIN users u ON a.created_by = u.id WHERE a.published = TRUE ORDER BY a.created_at DESC');
        Response::json(['success' => true, 'announcements' => $stmt->fetchAll()]);
    }

    public static function create(): void
    {
        $admin = Auth::requireRole('admin');
        $data = Utils::jsonBody();
        $title = trim((string)($data['title'] ?? ''));
        $content = trim((string)($data['content'] ?? ''));
        $published = (bool)($data['published'] ?? true);

        if ($title === '' || $content === '') {
            Response::error('Title and content required', 422);
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO announcements (title, content, created_by, published) VALUES (?, ?, ?, ?)');
        $stmt->execute([$title, $content, (int)$admin['id'], $published]);

        Response::json(['success' => true]);
    }

    public static function update(int $announcementId): void
    {
        Auth::requireRole('admin');
        $data = Utils::jsonBody();
        $title = trim((string)($data['title'] ?? ''));
        $content = trim((string)($data['content'] ?? ''));
        $published = (bool)($data['published'] ?? true);

        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE announcements SET title = ?, content = ?, published = ? WHERE id = ?');
        $stmt->execute([$title, $content, $published, $announcementId]);

        Response::json(['success' => true]);
    }

    public static function delete(int $announcementId): void
    {
        Auth::requireRole('admin');
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM announcements WHERE id = ?');
        $stmt->execute([$announcementId]);
        Response::json(['success' => true]);
    }
}
