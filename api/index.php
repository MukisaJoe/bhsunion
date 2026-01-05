<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/Response.php';
require_once __DIR__ . '/lib/Utils.php';
require_once __DIR__ . '/lib/RateLimiter.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/MembersController.php';
require_once __DIR__ . '/controllers/ContributionsController.php';
require_once __DIR__ . '/controllers/WithdrawalsController.php';
require_once __DIR__ . '/controllers/AnnouncementsController.php';
require_once __DIR__ . '/controllers/ChatController.php';
require_once __DIR__ . '/controllers/ReportsController.php';
require_once __DIR__ . '/controllers/MessagesController.php';
require_once __DIR__ . '/controllers/SettingsController.php';
require_once __DIR__ . '/controllers/AuditController.php';
require_once __DIR__ . '/controllers/ExportsController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/SavingsController.php';
require_once __DIR__ . '/controllers/ClientLogsController.php';
require_once __DIR__ . '/controllers/TreasuryController.php';

// Basic error handler to avoid empty responses on fatal errors.
register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Fatal error',
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
        ]);
    }
});

Utils::cors();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    Response::json(['success' => true]);
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = preg_replace('#^/api#', '', $path);
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

// Debug endpoint to check headers (remove in production)
if ($path === '/debug/headers') {
    $headers = [];
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    }
    Response::json([
        'success' => true,
        'http_authorization' => $_SERVER['HTTP_AUTHORIZATION'] ?? 'not set',
        'authorization' => $_SERVER['Authorization'] ?? 'not set',
        'redirect_http_authorization' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'not set',
        'all_headers' => $headers,
        'server_vars' => array_filter($_SERVER, function($key) {
            return stripos($key, 'auth') !== false || stripos($key, 'http') !== false;
        }, ARRAY_FILTER_USE_KEY)
    ]);
}

// Rate limiting (non-blocking - log errors but continue)
try {
    if ($path === '/auth/login') {
        RateLimiter::enforce('login', 10);
    } else {
        RateLimiter::enforce();
    }
} catch (Throwable $e) {
    // Log rate limiter errors but don't block requests
    error_log('Rate limiter error: ' . $e->getMessage());
}

switch (true) {
    case $method === 'GET' && $path === '/':
        Response::json(['success' => true, 'message' => 'Bhs Union API']);
        break;

    case $method === 'POST' && $path === '/auth/login':
        AuthController::login();
        break;

    case $method === 'GET' && $path === '/auth/me':
        AuthController::me();
        break;

    case $method === 'POST' && $path === '/auth/logout':
        AuthController::logout();
        break;

    case $method === 'POST' && $path === '/auth/change-password':
        AuthController::changePassword();
        break;

    case $method === 'GET' && $path === '/admin/members':
        MembersController::list();
        break;

    case $method === 'GET' && $path === '/members':
        MembersController::listPublic();
        break;

    case $method === 'POST' && $path === '/admin/members':
        MembersController::create();
        break;

    case $method === 'PATCH' && preg_match('#^/admin/members/(\d+)/status$#', $path, $matches):
        MembersController::updateStatus((int)$matches[1]);
        break;

    case $method === 'POST' && preg_match('#^/admin/members/(\d+)/reset-password$#', $path, $matches):
        MembersController::resetPassword((int)$matches[1]);
        break;

    case $method === 'GET' && $path === '/member/profile':
        MembersController::me();
        break;

    case $method === 'PUT' && $path === '/member/profile':
        MembersController::updateMe();
        break;

    case $method === 'POST' && $path === '/member/contributions':
        ContributionsController::create();
        break;

    case $method === 'GET' && $path === '/member/contributions':
        ContributionsController::listMember();
        break;

    case $method === 'GET' && $path === '/member/dashboard':
        DashboardController::member();
        break;

    case $method === 'GET' && $path === '/admin/contributions':
        ContributionsController::listAdmin();
        break;
    case $method === 'POST' && $path === '/admin/contributions':
        ContributionsController::createAdmin();
        break;

    case $method === 'POST' && preg_match('#^/admin/contributions/(\d+)/confirm$#', $path, $matches):
        ContributionsController::confirm((int)$matches[1]);
        break;
    case $method === 'POST' && preg_match('#^/admin/contributions/(\d+)/reject$#', $path, $matches):
        ContributionsController::reject((int)$matches[1]);
        break;
    case $method === 'POST' && preg_match('#^/member/contributions/(\d+)/cancel$#', $path, $matches):
        ContributionsController::cancel((int)$matches[1]);
        break;

    case $method === 'POST' && $path === '/admin/withdrawals':
        WithdrawalsController::create();
        break;

    case $method === 'GET' && $path === '/admin/withdrawals':
        WithdrawalsController::list();
        break;

    case $method === 'GET' && $path === '/admin/dashboard':
        DashboardController::admin();
        break;
    case $method === 'GET' && $path === '/member/overall-savings':
        SavingsController::member();
        break;
    case $method === 'GET' && $path === '/admin/overall-savings':
        SavingsController::admin();
        break;

    case $method === 'GET' && $path === '/announcements':
        AnnouncementsController::list();
        break;

    case $method === 'POST' && $path === '/admin/announcements':
        AnnouncementsController::create();
        break;

    case $method === 'PUT' && preg_match('#^/admin/announcements/(\d+)$#', $path, $matches):
        AnnouncementsController::update((int)$matches[1]);
        break;

    case $method === 'DELETE' && preg_match('#^/admin/announcements/(\d+)$#', $path, $matches):
        AnnouncementsController::delete((int)$matches[1]);
        break;

    case $method === 'GET' && $path === '/chat':
        ChatController::list();
        break;

    case $method === 'POST' && $path === '/chat':
        ChatController::create();
        break;

    case $method === 'PUT' && preg_match('#^/chat/(\\d+)$#', $path, $matches):
        ChatController::edit((int)$matches[1]);
        break;

    case $method === 'DELETE' && preg_match('#^/chat/(\\d+)$#', $path, $matches):
        ChatController::delete((int)$matches[1]);
        break;

    case $method === 'POST' && preg_match('#^/chat/(\\d+)/reactions$#', $path, $matches):
        ChatController::react((int)$matches[1]);
        break;

    case $method === 'GET' && $path === '/admin/reports':
        ReportsController::financial();
        break;

    case $method === 'GET' && $path === '/admin/messages':
        MessagesController::list();
        break;

    case $method === 'POST' && $path === '/admin/messages':
        MessagesController::create();
        break;

    case $method === 'GET' && $path === '/settings/monthly-amount':
        SettingsController::getMonthlyAmount();
        break;

    case $method === 'POST' && $path === '/admin/settings/monthly-amount':
        SettingsController::setMonthlyAmount();
        break;

    case $method === 'GET' && $path === '/settings/current-period':
        SettingsController::getCurrentPeriod();
        break;

    case $method === 'POST' && $path === '/client/logs':
        ClientLogsController::create();
        break;

    case $method === 'POST' && $path === '/admin/settings/current-period':
        SettingsController::setCurrentPeriod();
        break;

    case $method === 'POST' && $path === '/admin/treasury/adjust':
        TreasuryController::adjust();
        break;

    case $method === 'GET' && $path === '/admin/treasury/adjustments':
        TreasuryController::listAdjustments();
        break;

    case $method === 'GET' && $path === '/debug/headers':
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }
        Response::json([
            'success' => true,
            'http_authorization' => $_SERVER['HTTP_AUTHORIZATION'] ?? 'not set',
            'authorization' => $_SERVER['Authorization'] ?? 'not set',
            'redirect_http_authorization' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'not set',
            'all_headers' => $headers,
            'server_vars' => array_filter($_SERVER, function($key) {
                return stripos($key, 'auth') !== false || stripos($key, 'http') !== false;
            }, ARRAY_FILTER_USE_KEY)
        ]);
        break;

    case $method === 'GET' && $path === '/about':
        SettingsController::getAbout();
        break;

    case $method === 'PUT' && $path === '/admin/about':
        SettingsController::updateAbout();
        break;

    case $method === 'GET' && $path === '/admin/audit':
        AuditController::list();
        break;

    case $method === 'GET' && $path === '/admin/audit/export':
        AuditController::export();
        break;

    case $method === 'POST' && $path === '/admin/audit/rotate':
        AuditController::rotate();
        break;

    case $method === 'GET' && $path === '/admin/exports/contributions':
        ExportsController::contributions();
        break;
    case $method === 'GET' && $path === '/member/exports/contributions':
        ExportsController::memberContributions();
        break;

    case $method === 'GET' && $path === '/admin/exports/withdrawals':
        ExportsController::withdrawals();
        break;

    default:
        Response::error('Not found', 404);
}
