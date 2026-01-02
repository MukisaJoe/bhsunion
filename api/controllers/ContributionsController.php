<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Utils.php';

final class ContributionsController
{
    public static function create(): void
    {
        $user = Auth::requireRole('member');
        $data = Utils::jsonBody();
        $month = trim((string)($data['month'] ?? ''));
        $year = (int)($data['year'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);

        if ($month === '' || $year < 2000 || $amount <= 0) {
            Response::error('Invalid contribution data', 422);
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO contributions (member_id, month, year, amount) VALUES (?, ?, ?, ?)');
        $stmt->execute([(int)$user['id'], $month, $year, $amount]);

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$user['id'], "Submitted contribution of UGX {$amount} for {$month} {$year}"]);

        Response::json(['success' => true]);
    }

    public static function listMember(): void
    {
        $user = Auth::requireRole('member');
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id, month, year, amount, status, submitted_at, confirmed_at FROM contributions WHERE member_id = ? ORDER BY submitted_at DESC');
        $stmt->execute([(int)$user['id']]);
        Response::json(['success' => true, 'contributions' => $stmt->fetchAll()]);
    }

    public static function listAdmin(): void
    {
        Auth::requireRole('admin');
        $status = $_GET['status'] ?? null;
        $pdo = Database::connection();
        if ($status) {
            $stmt = $pdo->prepare('SELECT c.*, u.name AS member_name FROM contributions c JOIN users u ON c.member_id = u.id WHERE c.status = ? ORDER BY c.submitted_at DESC');
            $stmt->execute([$status]);
        } else {
            $stmt = $pdo->query('SELECT c.*, u.name AS member_name FROM contributions c JOIN users u ON c.member_id = u.id ORDER BY c.submitted_at DESC');
        }
        Response::json(['success' => true, 'contributions' => $stmt->fetchAll()]);
    }

    public static function confirm(int $contributionId): void
    {
        $admin = Auth::requireRole('admin');
        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE contributions SET status = "confirmed", confirmed_at = NOW(), confirmed_by = ? WHERE id = ?');
        $stmt->execute([(int)$admin['id'], $contributionId]);

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$admin['id'], "Confirmed contribution #{$contributionId}"]);

        Response::json(['success' => true]);
    }
}

