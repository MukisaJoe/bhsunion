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
        $stmt = $pdo->prepare('SELECT amount FROM monthly_settings WHERE month = ? AND year = ?');
        $stmt->execute([$month, $year]);
        if (!$stmt->fetch()) {
            Response::error('Contribution amount not set for this period', 422);
        }
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

    public static function createAdmin(): void
    {
        $admin = Auth::requireRole('admin');
        $data = Utils::jsonBody();
        $memberId = (int)($data['member_id'] ?? 0);
        $month = trim((string)($data['month'] ?? ''));
        $year = (int)($data['year'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);

        if ($memberId <= 0 || $month === '' || $year < 2000 || $amount <= 0) {
            Response::error('Invalid contribution data', 422);
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ? AND role = ?');
        $stmt->execute([$memberId, 'member']);
        if (!$stmt->fetch()) {
            Response::error('Member not found', 404);
        }

        $stmt = $pdo->prepare('SELECT amount FROM monthly_settings WHERE month = ? AND year = ?');
        $stmt->execute([$month, $year]);
        if (!$stmt->fetch()) {
            Response::error('Contribution amount not set for this period', 422);
        }

        $stmt = $pdo->prepare('SELECT id, status FROM contributions WHERE member_id = ? AND month = ? AND year = ? ORDER BY submitted_at DESC LIMIT 1');
        $stmt->execute([$memberId, $month, $year]);
        $existing = $stmt->fetch();
        if ($existing && $existing['status'] !== 'rejected') {
            Response::error('Contribution already exists for this period', 422);
        }

        $stmt = $pdo->prepare('INSERT INTO contributions (member_id, month, year, amount) VALUES (?, ?, ?, ?)');
        $stmt->execute([$memberId, $month, $year, $amount]);

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$admin['id'], "Submitted contribution for member #{$memberId} ({$month} {$year}, UGX {$amount})"]);

        Response::json(['success' => true]);
    }

    public static function confirm(int $contributionId): void
    {
        $admin = Auth::requireRole('admin');
        $pdo = Database::connection();
        $stmt = $pdo->prepare("UPDATE contributions SET status = 'confirmed', confirmed_at = NOW(), confirmed_by = ? WHERE id = ?");
        $stmt->execute([(int)$admin['id'], $contributionId]);

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$admin['id'], "Confirmed contribution #{$contributionId}"]);

        Response::json(['success' => true]);
    }

    public static function reject(int $contributionId): void
    {
        $admin = Auth::requireRole('admin');
        $pdo = Database::connection();
        $stmt = $pdo->prepare("SELECT c.id, c.amount, c.month, c.year, c.member_id, c.status, u.name AS member_name FROM contributions c JOIN users u ON c.member_id = u.id WHERE c.id = ?");
        $stmt->execute([$contributionId]);
        $row = $stmt->fetch();
        if (!$row) {
            Response::error('Contribution not found', 404);
        }
        if ($row['status'] !== 'pending') {
            Response::error('Only pending contributions can be deleted', 422);
        }

        $stmt = $pdo->prepare("UPDATE contributions SET status = 'rejected', confirmed_at = NULL, confirmed_by = NULL WHERE id = ?");
        $stmt->execute([$contributionId]);

        $amount = (float)$row['amount'];
        $month = $row['month'];
        $year = $row['year'];
        $memberName = $row['member_name'];

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$admin['id'], "Rejected contribution #{$contributionId} ({$memberName}, {$month} {$year}, UGX {$amount})"]);

        // Notify members via group chat message.
        $stmt = $pdo->prepare('INSERT INTO chat_messages (sender_id, message) VALUES (?, ?)');
        $stmt->execute([
            (int)$admin['id'],
            "Admin rejected {$memberName}'s contribution for {$month} {$year} (UGX " . number_format($amount, 0, '.', ',') . ").",
        ]);

        Response::json(['success' => true]);
    }

    public static function cancel(int $contributionId): void
    {
        $user = Auth::requireRole('member');
        $pdo = Database::connection();
        $stmt = $pdo->prepare("SELECT id, status FROM contributions WHERE id = ? AND member_id = ?");
        $stmt->execute([$contributionId, (int)$user['id']]);
        $row = $stmt->fetch();
        if (!$row) {
            Response::error('Contribution not found', 404);
        }
        if ($row['status'] !== 'pending') {
            Response::error('Only pending contributions can be cancelled', 422);
        }

        $stmt = $pdo->prepare("UPDATE contributions SET status = 'rejected', confirmed_at = NULL, confirmed_by = NULL WHERE id = ?");
        $stmt->execute([$contributionId]);

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$user['id'], "Cancelled contribution #{$contributionId}"]);

        Response::json(['success' => true]);
    }
}
