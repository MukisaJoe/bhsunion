<?php

declare(strict_types=1);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Bhs Union API health check',
]);
