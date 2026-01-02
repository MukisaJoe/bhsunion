<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Utils.php';

final class MembersController
{
    public static function list(): void
    {
        Auth::requireRole('admin');
        $status = $_GET['status'] ?? null;

        $pdo = Database::connection();
        if ($status) {
            $stmt = $pdo->prepare("SELECT id, name, email, role, status, phone, created_at FROM users WHERE role = 'member' AND status = ? ORDER BY created_at DESC");
            $stmt->execute([$status]);
        } else {
            $stmt = $pdo->query("SELECT id, name, email, role, status, phone, created_at FROM users WHERE role = 'member' ORDER BY created_at DESC");
        }
        $members = $stmt->fetchAll();
        Response::json(['success' => true, 'members' => $members]);
    }

    public static function create(): void
    {
        $admin = Auth::requireRole('admin');
        $data = Utils::jsonBody();
        $name = trim((string)($data['name'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $phone = trim((string)($data['phone'] ?? ''));

        if ($name === '' || $email === '' || $password === '') {
            Response::error('Name, email, and password required', 422);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $pdo = Database::connection();
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status, phone) VALUES (?, ?, ?, 'member', 'active', ?)");
        $stmt->execute([$name, $email, $hash, $phone]);

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$admin['id'], "Created member account for {$name}"]);

        Response::json(['success' => true]);
    }

    public static function updateStatus(int $memberId): void
    {
        $admin = Auth::requireRole('admin');
        $data = Utils::jsonBody();
        $status = $data['status'] ?? '';
        $allowed = ['active', 'pending', 'disabled'];
        if (!in_array($status, $allowed, true)) {
            Response::error('Invalid status', 422);
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'member'");
        $stmt->execute([$status, $memberId]);

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$admin['id'], "Updated member #{$memberId} status to {$status}"]);

        Response::json(['success' => true]);
    }

    public static function resetPassword(int $memberId): void
    {
        $admin = Auth::requireRole('admin');
        $data = Utils::jsonBody();
        $newPassword = (string)($data['new_password'] ?? 'Bhs2016');

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo = Database::connection();
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ? AND role = 'member'");
        $stmt->execute([$hash, $memberId]);

        $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_id, action) VALUES (?, ?)');
        $stmt->execute([(int)$admin['id'], "Reset password for member #{$memberId}"]);

        Response::json(['success' => true]);
    }

    public static function me(): void
    {
        $user = Auth::requireUser();
        Response::json([
            'success' => true,
            'profile' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'provider' => $user['provider'],
                'mobile_money_number' => $user['mobile_money_number'],
                'other_number' => $user['other_number'],
                'status' => $user['status'],
                'role' => $user['role'],
            ],
        ]);
    }

    public static function updateMe(): void
    {
        $user = Auth::requireUser();
        $data = Utils::jsonBody();
        $name = trim((string)($data['name'] ?? $user['name']));
        $phone = trim((string)($data['phone'] ?? $user['phone']));
        $provider = trim((string)($data['provider'] ?? $user['provider']));
        $mobile = trim((string)($data['mobile_money_number'] ?? $user['mobile_money_number']));
        $other = trim((string)($data['other_number'] ?? $user['other_number']));

        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ?, provider = ?, mobile_money_number = ?, other_number = ? WHERE id = ?');
        $stmt->execute([$name, $phone, $provider, $mobile, $other, (int)$user['id']]);

        Response::json(['success' => true]);
    }
}
