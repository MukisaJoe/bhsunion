<?php
// Render entry point - PHP built-in server router
// This file is in public/ directory which is the web root

// Get the request path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Remove query string from path for routing
$pathWithoutQuery = strtok($requestPath, '?');

// If the path starts with /api, remove it (api/index.php expects paths without /api prefix)
// Otherwise, route directly to api/index.php
if (strpos($pathWithoutQuery, '/api') === 0) {
    // Path already has /api, remove it for the API router
    $_SERVER['REQUEST_URI'] = str_replace('/api', '', $requestUri, 1);
}

// Route everything to the API
require_once __DIR__ . '/../api/index.php';
