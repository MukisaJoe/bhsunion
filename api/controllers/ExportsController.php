<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';

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

