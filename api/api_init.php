<?php
/**
 * API initialization - must be required FIRST by all checker API endpoints.
 * Ensures JSON-only output (no PHP error HTML) and sets Content-Type.
 */
declare(strict_types=1);

// Suppress HTML errors - API must return pure JSON only
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');

// Custom shutdown handler for fatal errors - output JSON instead of HTML
register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode([
            'status' => 'dead',
            'Response' => 'Internal error. Please try again.',
            'Gateway' => 'API',
            'cc' => $_GET['cc'] ?? '',
            'credits' => 0,
            'brand' => 'UNKNOWN',
            'card_type' => 'UNKNOWN',
            'level' => 'STANDARD',
            'issuer' => 'Unknown',
            'country_info' => 'Unknown'
        ]);
    }
});
