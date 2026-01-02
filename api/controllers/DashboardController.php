<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';

final class DashboardController
{
    public static function admin(): void
    {
        Auth::requireRole('admin');
        $pdo = Database::connection();

        [$monthName, $year] = self::currentPeriod($pdo);

        $totalMembers = self::countMembers($pdo);
        $activeMembers = self::countMembers($pdo, 'active');
        $pendingMembers = self::countMembers($pdo, 'pending');

        $pendingPayments = self::countContributions($pdo, $monthName, $year, 'pending');
        $paidMembers = self::countPaidMembers($pdo, $monthName, $year);
        $paymentTotal = $activeMembers > 0 ? $activeMembers : $totalMembers;

        $treasuryBalance = self::treasuryBalance($pdo);
        $latestPayments = self::latestPayments($pdo);

        $pendingMemberList = self::pendingMembers($pdo);
        $alerts = self::alerts($activeMembers, $paidMembers, $pendingPayments);
        $recentActivities = self::recentActivities($pdo);

        Response::json([
            'success' => true,
            'period' => ['month' => $monthName, 'year' => $year],
            'treasury_balance' => $treasuryBalance,
            'latest_payments' => $latestPayments,
            'stats' => [
                'total_members' => $totalMembers,
                'pending_members' => $pendingMembers,
                'pending_payments' => $pendingPayments,
                'paid_members' => $paidMembers,
                'payment_total' => $paymentTotal,
            ],
            'pending_members' => $pendingMemberList,
            'alerts' => $alerts,
            'recent_activities' => $recentActivities,
        ]);
    }

    public static function member(): void
    {
        $user = Auth::requireRole('member');
        $pdo = Database::connection();

        [$monthName, $year] = self::currentPeriod($pdo);

        $treasuryBalance = self::treasuryBalance($pdo);
        $latestPayments = self::latestPayments($pdo);
        $announcements = self::latestAnnouncements($pdo, 2);
        $activity = self::memberActivity($pdo, (int)$user['id']);

        Response::json([
            'success' => true,
            'period' => ['month' => $monthName, 'year' => $year],
            'treasury_balance' => $treasuryBalance,
            'latest_payments' => $latestPayments,
            'announcements' => $announcements,
            'activity' => $activity,
        ]);
    }

    private static function currentPeriod(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT month, year FROM monthly_settings ORDER BY created_at DESC LIMIT 1');
        $row = $stmt->fetch();
        if ($row) {
            return [$row['month'], (int)$row['year']];
        }

        $monthName = date('F');
        $year = (int)date('Y');
        return [$monthName, $year];
    }

    private static function countMembers(PDO $pdo, ?string $status = null): int
    {
        if ($status === null) {
            $stmt = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'member'");
            $row = $stmt->fetch();
            return (int)$row['total'];
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'member' AND status = ?");
        $stmt->execute([$status]);
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    private static function countContributions(PDO $pdo, string $month, int $year, string $status): int
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM contributions WHERE month = ? AND year = ? AND status = ?');
        $stmt->execute([$month, $year, $status]);
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    private static function countPaidMembers(PDO $pdo, string $month, int $year): int
    {
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT member_id) AS total FROM contributions WHERE month = ? AND year = ? AND status = 'confirmed'");
        $stmt->execute([$month, $year]);
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    private static function treasuryBalance(PDO $pdo): float
    {
        // Use the database function for accurate balance calculation
        $stmt = $pdo->query('SELECT get_treasury_balance() AS balance');
        return (float)$stmt->fetch()['balance'];
    }

    private static function latestPayments(PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT c.amount, c.month, c.year, c.confirmed_at, u.name AS member_name FROM contributions c JOIN users u ON c.member_id = u.id WHERE c.status = 'confirmed' ORDER BY c.confirmed_at DESC LIMIT 5");
        return $stmt->fetchAll();
    }

    private static function pendingMembers(PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'member' AND status = 'pending' ORDER BY created_at DESC LIMIT 5");
        return $stmt->fetchAll();
    }

    private static function alerts(int $activeMembers, int $paidMembers, int $pendingPayments): array
    {
        $overdue = $activeMembers - $paidMembers - $pendingPayments;
        $overdue = $overdue < 0 ? 0 : $overdue;
        if ($overdue === 0) {
            return [];
        }
        return [
            [
                'title' => '⚠️ Overdue Contributions',
                'message' => $overdue . ' active members have not submitted this month.',
            ],
        ];
    }

    private static function recentActivities(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT action, created_at FROM audit_logs ORDER BY created_at DESC LIMIT 5');
        $rows = $stmt->fetchAll();
        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'icon' => '📝',
                'title' => $row['action'],
                'meta' => $row['created_at'],
            ];
        }
        return $items;
    }

    private static function latestAnnouncements(PDO $pdo, int $limit): array
    {
        $stmt = $pdo->prepare('SELECT id, title, content, created_at FROM announcements WHERE published = TRUE ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private static function memberActivity(PDO $pdo, int $memberId): array
    {
        $activity = [];
        $stmt = $pdo->prepare('SELECT month, year, amount, status, submitted_at, confirmed_at FROM contributions WHERE member_id = ? ORDER BY submitted_at DESC LIMIT 5');
        $stmt->execute([$memberId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $status = $row['status'];
            $label = $status === 'confirmed'
                ? 'Admin confirmed your contribution'
                : 'You submitted a contribution';
            $time = $status === 'confirmed' ? $row['confirmed_at'] : $row['submitted_at'];
            $activity[] = [
                'icon' => $status === 'confirmed' ? '✅' : '✓',
                'text' => $label . ' • UGX ' . number_format((float)$row['amount'], 0, '.', ',') . ' (' . $row['month'] . ' ' . $row['year'] . ')',
                'time' => $time,
            ];
        }

        $stmt = $pdo->query('SELECT title, created_at FROM announcements WHERE published = TRUE ORDER BY created_at DESC LIMIT 1');
        $announcement = $stmt->fetch();
        if ($announcement) {
            $activity[] = [
                'icon' => '📢',
                'text' => $announcement['title'],
                'time' => $announcement['created_at'],
            ];
        }

        usort($activity, static function (array $a, array $b): int {
            return strcmp((string)$b['time'], (string)$a['time']);
        });

        return array_slice($activity, 0, 4);
    }
}
