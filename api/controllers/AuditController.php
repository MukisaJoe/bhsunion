<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';

final class AuditController
{
    public static function list(): void
    {
        Auth::requireRole('admin');
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT a.id, a.action, a.created_at, u.name AS actor_name FROM audit_logs a JOIN users u ON a.actor_id = u.id ORDER BY a.created_at DESC');
        Response::json(['success' => true, 'logs' => $stmt->fetchAll()]);
    }

    public static function export(): void
    {
        Auth::requireRole('admin');
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT a.created_at, u.name AS actor_name, a.action FROM audit_logs a JOIN users u ON a.actor_id = u.id ORDER BY a.created_at DESC');
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[] = [$row['created_at'], $row['actor_name'], $row['action']];
        }
        Response::csv('audit_logs.csv', ['Date', 'Actor', 'Action'], $rows);
    }

    public static function rotate(): void
    {
        Auth::requireRole('admin');
        $days = (int)($_GET['days'] ?? 90);
        if ($days < 7) {
            Response::error('Minimum rotation is 7 days', 422);
        }
        $pdo = Database::connection();
        $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE created_at < (NOW() - (? || ' days')::interval)");
        $stmt->execute([(string)$days]);
        Response::json(['success' => true, 'message' => 'Audit logs rotated']);
    }
}
