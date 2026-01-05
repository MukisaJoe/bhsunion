<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';

final class SavingsController
{
    public static function member(): void
    {
        $user = Auth::requireRole('member');
        $pdo = Database::connection();

        $total = self::totalConfirmed($pdo);
        $memberTotal = self::memberConfirmed($pdo, (int)$user['id']);
        $percentage = $total > 0 ? round(($memberTotal / $total) * 100, 2) : 0.0;

        Response::json([
            'success' => true,
            'total_confirmed' => $total,
            'member_confirmed' => $memberTotal,
            'member_percentage' => $percentage,
        ]);
    }

    public static function admin(): void
    {
        Auth::requireRole('admin');
        $pdo = Database::connection();

        $total = self::totalConfirmed($pdo);
        $members = self::memberBreakdown($pdo, $total);

        Response::json([
            'success' => true,
            'total_confirmed' => $total,
            'members' => $members,
        ]);
    }

    private static function totalConfirmed(PDO $pdo): float
    {
        $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) AS total FROM contributions WHERE status = 'confirmed'");
        $row = $stmt->fetch();
        return (float)$row['total'];
    }

    private static function memberConfirmed(PDO $pdo, int $memberId): float
    {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM contributions WHERE status = 'confirmed' AND member_id = ?");
        $stmt->execute([$memberId]);
        $row = $stmt->fetch();
        return (float)$row['total'];
    }

    private static function memberBreakdown(PDO $pdo, float $total): array
    {
        $stmt = $pdo->query(
            "SELECT u.id, u.name, u.email, COALESCE(SUM(c.amount), 0) AS total
             FROM users u
             LEFT JOIN contributions c ON c.member_id = u.id AND c.status = 'confirmed'
             WHERE u.role = 'member'
             GROUP BY u.id
             ORDER BY total DESC, u.name ASC"
        );
        $rows = $stmt->fetchAll();
        $items = [];
        foreach ($rows as $row) {
            $memberTotal = (float)$row['total'];
            $percentage = $total > 0 ? round(($memberTotal / $total) * 100, 2) : 0.0;
            $items[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'total' => $memberTotal,
                'percentage' => $percentage,
            ];
        }
        return $items;
    }
}
