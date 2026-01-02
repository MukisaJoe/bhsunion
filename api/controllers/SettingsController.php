<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Utils.php';

final class SettingsController
{
    public static function getMonthlyAmount(): void
    {
        Auth::requireUser();
        $month = trim((string)($_GET['month'] ?? ''));
        $year = (int)($_GET['year'] ?? 0);
        if ($month === '' || $year < 2000) {
            Response::error('Month and year required', 422);
        }
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT amount FROM monthly_settings WHERE month = ? AND year = ?');
        $stmt->execute([$month, $year]);
        $row = $stmt->fetch();
        Response::json(['success' => true, 'amount' => $row ? (float)$row['amount'] : null]);
    }

    public static function setMonthlyAmount(): void
    {
        $admin = Auth::requireRole('admin');
        $data = Utils::jsonBody();
        $month = trim((string)($data['month'] ?? ''));
        $year = (int)($data['year'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);
        if ($month === '' || $year < 2000 || $amount <= 0) {
            Response::error('Invalid month settings', 422);
        }
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO monthly_settings (month, year, amount, set_by) VALUES (?, ?, ?, ?)
             ON CONFLICT (month, year) DO UPDATE SET amount = EXCLUDED.amount, set_by = EXCLUDED.set_by'
        );
        $stmt->execute([$month, $year, $amount, (int)$admin['id']]);

        Response::json(['success' => true]);
    }

    public static function setCurrentPeriod(): void
    {
        $admin = Auth::requireRole('admin');
        $data = Utils::jsonBody();
        $month = trim((string)($data['month'] ?? ''));
        $year = (int)($data['year'] ?? 0);
        if ($month === '' || $year < 2000) {
            Response::error('Invalid period', 422);
        }
        $pdo = Database::connection();
        $value = json_encode(['month' => $month, 'year' => $year]);
        $stmt = $pdo->prepare(
            'INSERT INTO app_settings (setting_key, setting_value) VALUES (\'current_period\', ?)
             ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value, updated_at = NOW()'
        );
        $stmt->execute([$value]);

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$admin['id'], "Set current contribution period to {$month} {$year}"]);

        Response::json(['success' => true]);
    }

    public static function getCurrentPeriod(): void
    {
        Auth::requireUser();
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT setting_value FROM app_settings WHERE setting_key = "current_period"');
        $stmt->execute();
        $row = $stmt->fetch();
        $value = $row ? json_decode($row['setting_value'], true) : null;
        if (!$value || !isset($value['month'], $value['year'])) {
            $stmt = $pdo->query('SELECT month, year FROM monthly_settings ORDER BY created_at DESC LIMIT 1');
            $fallback = $stmt->fetch();
            if ($fallback) {
                $value = ['month' => $fallback['month'], 'year' => (int)$fallback['year']];
            } else {
                $value = ['month' => date('F'), 'year' => (int)date('Y')];
            }

            $stmt = $pdo->prepare(
                'INSERT INTO app_settings (setting_key, setting_value) VALUES (\'current_period\', ?)
                 ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value, updated_at = NOW()'
            );
            $stmt->execute([json_encode($value)]);
        }
        Response::json(['success' => true, 'period' => $value]);
    }

    public static function getAbout(): void
    {
        Auth::requireUser();
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT setting_key, setting_value FROM app_settings WHERE setting_key IN ("about_content", "account_details")');
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $result = ['about_content' => '', 'account_details' => ''];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        Response::json(['success' => true, 'about' => $result]);
    }

    public static function updateAbout(): void
    {
        Auth::requireRole('admin');
        $data = Utils::jsonBody();
        $about = trim((string)($data['about_content'] ?? ''));
        $account = trim((string)($data['account_details'] ?? ''));

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO app_settings (setting_key, setting_value) VALUES (\'about_content\', ?)
             ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value, updated_at = NOW()'
        );
        $stmt->execute([$about]);
        $stmt = $pdo->prepare(
            'INSERT INTO app_settings (setting_key, setting_value) VALUES (\'account_details\', ?)
             ON CONFLICT (setting_key) DO UPDATE SET setting_value = EXCLUDED.setting_value, updated_at = NOW()'
        );
        $stmt->execute([$account]);

        Response::json(['success' => true]);
    }
}
