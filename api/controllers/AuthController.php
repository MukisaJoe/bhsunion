<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Utils.php';

final class AuthController
{
    public static function login(): void
    {
        $data = Utils::jsonBody();
        $email = trim((string)($data['email'] ?? ''));
        $password = (string)($data['password'] ?? '');

        if ($email === '' || $password === '') {
            Response::error('Email and password required', 422);
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Response::error('Invalid credentials', 401);
        }

        if ($user['status'] !== 'active') {
            Response::error('Account not active', 403);
        }

        $token = Auth::issueToken((int)$user['id']);
        Response::json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => (int)$user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role'],
                'status' => $user['status'],
            ],
        ]);
    }

    public static function me(): void
    {
        $user = Auth::requireUser();
        Response::json([
            'success' => true,
            'user' => [
                'id' => (int)$user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role'],
                'status' => $user['status'],
                'phone' => $user['phone'],
                'provider' => $user['provider'],
                'mobile_money_number' => $user['mobile_money_number'],
                'other_number' => $user['other_number'],
            ],
        ]);
    }

    public static function logout(): void
    {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (stripos($token, 'Bearer ') === 0) {
            $token = trim(substr($token, 7));
            Auth::revokeToken($token);
        }
        Response::json(['success' => true]);
    }

    public static function changePassword(): void
    {
        $user = Auth::requireUser();
        $data = Utils::jsonBody();
        $current = (string)($data['current_password'] ?? '');
        $next = (string)($data['new_password'] ?? '');

        if ($current === '' || $next === '') {
            Response::error('Current and new password required', 422);
        }

        if (!password_verify($current, $user['password_hash'])) {
            Response::error('Current password is incorrect', 403);
        }

        $hash = password_hash($next, PASSWORD_DEFAULT);
        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hash, (int)$user['id']]);

        Response::json(['success' => true]);
    }
}
