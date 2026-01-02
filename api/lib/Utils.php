<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

final class Utils
{
    public static function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function cors(): void
    {
        header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGINS);
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    }

    public static function monthIndex(string $month): ?int
    {
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $index = array_search($month, $months, true);
        if ($index === false) {
            return null;
        }
        return $index + 1;
    }
}

