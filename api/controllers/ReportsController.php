<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';

final class ReportsController
{
    public static function financial(): void
    {
        Auth::requireRole('admin');
        $pdo = Database::connection();
        $month = (int)date('n');
        $year = (int)date('Y');
        $quarter = intdiv($month - 1, 3) + 1;

        $monthlyContrib = self::sumContributions($pdo, $month, $year);
        $monthlyWithdrawals = self::sumWithdrawals($pdo, $month, $year);
        $quarterMonths = [$month, $month - 1, $month - 2];
        $quarterContrib = self::sumContributionsForMonths($pdo, $quarterMonths, $year);
        $quarterWithdrawals = self::sumWithdrawalsForMonths($pdo, $quarterMonths, $year);

        $totalMembers = self::countMembers($pdo);
        $activeMembers = self::countMembers($pdo, 'active');
        $pendingMembers = self::countMembers($pdo, 'pending');
        $disabledMembers = self::countMembers($pdo, 'disabled');
        $newMembers = self::countMembersCreated($pdo, $month, $year);

        $submittedContrib = self::countContributions($pdo, $month, $year);
        $confirmedContrib = self::countContributions($pdo, $month, $year, 'confirmed');
        $pendingContrib = self::countContributions($pdo, $month, $year, 'pending');
        $withdrawalCount = self::countWithdrawals($pdo, $month, $year);
        $announcementCount = self::countAnnouncements($pdo, $month, $year);

        Response::json([
            'success' => true,
            'reports' => [
                [
                    'title' => 'Monthly Contribution Report',
                    'period' => date('F Y'),
                    'type' => 'financial',
                    'stats' => [
                        ['label' => 'Confirmed Contributions', 'value' => $monthlyContrib],
                        ['label' => 'Admin Withdrawals', 'value' => $monthlyWithdrawals],
                    ],
                    'date' => date('d M Y'),
                ],
                [
                    'title' => 'Quarterly Treasury Summary',
                    'period' => 'Q' . $quarter . ' ' . $year,
                    'type' => 'financial',
                    'stats' => [
                        ['label' => 'Confirmed Contributions', 'value' => $quarterContrib],
                        ['label' => 'Admin Withdrawals', 'value' => $quarterWithdrawals],
                    ],
                    'date' => date('d M Y'),
                ],
                [
                    'title' => 'Member Directory Overview',
                    'period' => date('F Y'),
                    'type' => 'members',
                    'stats' => [
                        ['label' => 'Total Members', 'value' => self::formatCount($totalMembers)],
                        ['label' => 'Active Members', 'value' => self::formatCount($activeMembers)],
                        ['label' => 'Pending Approvals', 'value' => self::formatCount($pendingMembers)],
                        ['label' => 'Disabled Members', 'value' => self::formatCount($disabledMembers)],
                    ],
                    'date' => date('d M Y'),
                ],
                [
                    'title' => 'New Members This Month',
                    'period' => date('F Y'),
                    'type' => 'members',
                    'stats' => [
                        ['label' => 'New Members', 'value' => self::formatCount($newMembers)],
                        ['label' => 'Active Members', 'value' => self::formatCount($activeMembers)],
                    ],
                    'date' => date('d M Y'),
                ],
                [
                    'title' => 'Contribution Activity',
                    'period' => date('F Y'),
                    'type' => 'activity',
                    'stats' => [
                        ['label' => 'Submitted', 'value' => self::formatCount($submittedContrib)],
                        ['label' => 'Confirmed', 'value' => self::formatCount($confirmedContrib)],
                        ['label' => 'Pending', 'value' => self::formatCount($pendingContrib)],
                    ],
                    'date' => date('d M Y'),
                ],
                [
                    'title' => 'Admin Actions',
                    'period' => date('F Y'),
                    'type' => 'activity',
                    'stats' => [
                        ['label' => 'Withdrawals', 'value' => self::formatCount($withdrawalCount)],
                        ['label' => 'Announcements', 'value' => self::formatCount($announcementCount)],
                    ],
                    'date' => date('d M Y'),
                ],
            ],
        ]);
    }

    private static function sumContributions(PDO $pdo, int $month, int $year): string
    {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM contributions WHERE status = 'confirmed' AND year = ? AND month = ?");
        $stmt->execute([$year, self::monthName($month)]);
        $row = $stmt->fetch();
        return 'UGX ' . number_format((float)$row['total'], 0, '.', ',');
    }

    private static function sumContributionsForMonths(PDO $pdo, array $months, int $year): string
    {
        $monthNames = array_map([self::class, 'monthName'], $months);
        $placeholders = implode(',', array_fill(0, count($monthNames), '?'));
        $params = array_merge([$year], $monthNames);
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM contributions WHERE status = 'confirmed' AND year = ? AND month IN (" . $placeholders . ')');
        $stmt->execute($params);
        $row = $stmt->fetch();
        return 'UGX ' . number_format((float)$row['total'], 0, '.', ',');
    }

    private static function sumWithdrawals(PDO $pdo, int $month, int $year): string
    {
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) AS total FROM withdrawals WHERE EXTRACT(YEAR FROM created_at) = ? AND EXTRACT(MONTH FROM created_at) = ?');
        $stmt->execute([$year, $month]);
        $row = $stmt->fetch();
        return 'UGX ' . number_format((float)$row['total'], 0, '.', ',');
    }

    private static function sumWithdrawalsForMonths(PDO $pdo, array $months, int $year): string
    {
        $placeholders = implode(',', array_fill(0, count($months), '?'));
        $params = array_merge([$year], $months);
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) AS total FROM withdrawals WHERE EXTRACT(YEAR FROM created_at) = ? AND EXTRACT(MONTH FROM created_at) IN (' . $placeholders . ')');
        $stmt->execute($params);
        $row = $stmt->fetch();
        return 'UGX ' . number_format((float)$row['total'], 0, '.', ',');
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

    private static function countMembersCreated(PDO $pdo, int $month, int $year): int
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM users WHERE role = \'member\' AND EXTRACT(YEAR FROM created_at) = ? AND EXTRACT(MONTH FROM created_at) = ?');
        $stmt->execute([$year, $month]);
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    private static function countContributions(PDO $pdo, int $month, int $year, ?string $status = null): int
    {
        $monthName = self::monthName($month);
        if ($status === null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM contributions WHERE year = ? AND month = ?');
            $stmt->execute([$year, $monthName]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM contributions WHERE year = ? AND month = ? AND status = ?');
            $stmt->execute([$year, $monthName, $status]);
        }
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    private static function countWithdrawals(PDO $pdo, int $month, int $year): int
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM withdrawals WHERE EXTRACT(YEAR FROM created_at) = ? AND EXTRACT(MONTH FROM created_at) = ?');
        $stmt->execute([$year, $month]);
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    private static function countAnnouncements(PDO $pdo, int $month, int $year): int
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM announcements WHERE EXTRACT(YEAR FROM created_at) = ? AND EXTRACT(MONTH FROM created_at) = ?');
        $stmt->execute([$year, $month]);
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    private static function formatCount(int $value): string
    {
        return number_format($value, 0, '.', ',');
    }

    private static function monthName(int $month): string
    {
        $names = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
        return $names[$month] ?? 'January';
    }
}
