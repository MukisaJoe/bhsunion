<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Utils.php';

final class WithdrawalsController
{
    public static function create(): void
    {
        $admin = Auth::requireRole('admin');
        $data = Utils::jsonBody();
        $amount = (float)($data['amount'] ?? 0);
        $reason = trim((string)($data['reason'] ?? ''));

        if ($amount <= 0 || $reason === '') {
            Response::error('Amount and reason required', 422);
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO withdrawals (admin_id, amount, reason) VALUES (?, ?, ?)');
        $stmt->execute([(int)$admin['id'], $amount, $reason]);

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$admin['id'], "Withdrew UGX {$amount}. Reason: {$reason}"]);

        Response::json(['success' => true]);
    }

    public static function list(): void
    {
        Auth::requireRole('admin');
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT w.*, u.name AS admin_name FROM withdrawals w JOIN users u ON w.admin_id = u.id ORDER BY w.created_at DESC');
        Response::json(['success' => true, 'withdrawals' => $stmt->fetchAll()]);
    }
}

