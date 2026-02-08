<?php
/**
 * Health check endpoint for Railway
 * Returns 200 OK if the application is running
 */

// Disable session for health check
define('NO_SESSION', true);

header('Content-Type: application/json');
http_response_code(200);

echo json_encode([
    'status' => 'ok',
    'timestamp' => time(),
    'php_version' => PHP_VERSION,
    'server' => 'PHP Built-in Server'
]);
exit;
