<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Utils.php';

final class ExportsController
{
    public static function contributions(): void
    {
        Auth::requireRole('admin');
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT c.id, u.name AS member_name, c.month, c.year, c.amount, c.status, c.submitted_at, c.confirmed_at FROM contributions c JOIN users u ON c.member_id = u.id ORDER BY c.submitted_at DESC');
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[] = [
                $row['id'],
                $row['member_name'],
                $row['month'],
                $row['year'],
                $row['amount'],
                $row['status'],
                $row['submitted_at'],
                $row['confirmed_at'],
            ];
        }
        Response::csv('contributions.csv', ['ID', 'Member', 'Month', 'Year', 'Amount', 'Status', 'Submitted At', 'Confirmed At'], $rows);
    }

    public static function memberContributions(): void
    {
        $user = Auth::requireRole('member');
        $startMonth = $_GET['start_month'] ?? null;
        $startYear = isset($_GET['start_year']) ? (int)$_GET['start_year'] : null;
        $endMonth = $_GET['end_month'] ?? null;
        $endYear = isset($_GET['end_year']) ? (int)$_GET['end_year'] : null;

        $startIndex = $startMonth ? Utils::monthIndex((string)$startMonth) : null;
        $endIndex = $endMonth ? Utils::monthIndex((string)$endMonth) : null;
        $hasRange = $startIndex !== null && $endIndex !== null && $startYear && $endYear;

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT id, month, year, amount, status, submitted_at, confirmed_at
             FROM contributions
             WHERE member_id = ?
             ORDER BY submitted_at DESC'
        );
        $stmt->execute([(int)$user['id']]);
        $rows = [];
        while ($row = $stmt->fetch()) {
            if ($hasRange) {
                $monthIndex = Utils::monthIndex((string)$row['month']);
                if ($monthIndex === null) {
                    continue;
                }
                $cursor = ((int)$row['year']) * 12 + $monthIndex;
                $startCursor = $startYear * 12 + $startIndex;
                $endCursor = $endYear * 12 + $endIndex;
                if ($cursor < $startCursor || $cursor > $endCursor) {
                    continue;
                }
            }
            $rows[] = [
                $row['id'],
                $row['month'],
                $row['year'],
                $row['amount'],
                $row['status'],
                $row['submitted_at'],
                $row['confirmed_at'],
            ];
        }

        Response::csv(
            'my_contributions.csv',
            ['ID', 'Month', 'Year', 'Amount', 'Status', 'Submitted At', 'Confirmed At'],
            $rows
        );
    }

    public static function withdrawals(): void
    {
        Auth::requireRole('admin');
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT w.id, u.name AS admin_name, w.amount, w.reason, w.created_at FROM withdrawals w JOIN users u ON w.admin_id = u.id ORDER BY w.created_at DESC');
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[] = [
                $row['id'],
                $row['admin_name'],
                $row['amount'],
                $row['reason'],
                $row['created_at'],
            ];
        }
        Response::csv('withdrawals.csv', ['ID', 'Admin', 'Amount', 'Reason', 'Date'], $rows);
    }
}
