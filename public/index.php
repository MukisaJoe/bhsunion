<?php
// Apache entry point router
// This file handles requests that don't match files/directories

// Get the request path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Remove query string from path for routing
$pathWithoutQuery = strtok($requestPath, '?');

// If the path doesn't start with /api, treat it as an API request (for root requests)
// If it does start with /api, it should have been handled by .htaccess rewrite rule
// But if we get here, route to API anyway
if (strpos($pathWithoutQuery, '/api') !== 0 && $pathWithoutQuery !== '/') {
    // For non-root, non-api paths, route to API
    $_SERVER['REQUEST_URI'] = $requestUri;
}

// Route everything to the API
require_once __DIR__ . '/../api/index.php';
