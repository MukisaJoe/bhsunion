<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Utils.php';

final class TreasuryController
{
    public static function adjust(): void
    {
        $admin = Auth::requireRole('admin');
        $data = Utils::jsonBody();
        $amount = (float)($data['amount'] ?? 0);
        $adjustmentType = trim((string)($data['adjustment_type'] ?? 'add'));
        $reason = trim((string)($data['reason'] ?? 'Treasury adjustment'));

        if ($amount <= 0) {
            Response::error('Amount must be greater than zero', 422);
        }

        $allowedTypes = ['add', 'withdraw', 'initial', 'correction'];
        if (!in_array($adjustmentType, $allowedTypes, true)) {
            Response::error('Invalid adjustment type', 422);
        }

        $pdo = Database::connection();
        
        // Get current treasury balance
        $stmt = $pdo->query('SELECT get_treasury_balance() AS balance');
        $currentBalance = (float)$stmt->fetch()['balance'];
        
        // Calculate new balance
        $newBalance = $currentBalance;
        if ($adjustmentType === 'add' || $adjustmentType === 'initial' || $adjustmentType === 'correction') {
            $newBalance += $amount;
        } else if ($adjustmentType === 'withdraw') {
            $newBalance -= $amount;
        }

        // Insert adjustment
        $stmt = $pdo->prepare('INSERT INTO treasury_adjustments (admin_id, adjustment_type, amount, reason, previous_balance, new_balance) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int)$admin['id'],
            $adjustmentType,
            $amount,
            $reason,
            $currentBalance,
            $newBalance
        ]);

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$admin['id'], "Treasury {$adjustmentType}: UGX {$amount} - {$reason}"]);

        Response::json([
            'success' => true,
            'previous_balance' => $currentBalance,
            'new_balance' => $newBalance,
            'adjustment' => $amount
        ]);
    }

    public static function listAdjustments(): void
    {
        Auth::requireRole('admin');
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT t.id, t.adjustment_type, t.amount, t.reason, t.previous_balance, t.new_balance, t.created_at, u.name AS admin_name FROM treasury_adjustments t JOIN users u ON u.id = t.admin_id ORDER BY t.created_at DESC');
        Response::json(['success' => true, 'adjustments' => $stmt->fetchAll()]);
    }
}
