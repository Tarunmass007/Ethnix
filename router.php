<?php
/**
 * Router script for PHP built-in server
 * This handles routing when using php -S command
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Health check endpoint (no session needed)
if ($_SERVER['REQUEST_URI'] === '/health' || $_SERVER['REQUEST_URI'] === '/health.php') {
    require __DIR__ . '/health.php';
    exit;
}

// Log function for debugging
function routerLog($message) {
    error_log("[ROUTER] " . $message);
}

// Get the requested URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
routerLog("Request URI: " . $uri);

// Serve static files directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    $filePath = __DIR__ . $uri;
    
    // Check if it's a directory
    if (is_dir($filePath)) {
        routerLog("Directory requested: " . $uri);
        return false;
    }
    
    // Get file extension
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    // Define MIME types for common static files
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'eot'  => 'application/vnd.ms-fontobject',
    ];
    
    // If it's a static file, serve it with proper MIME type
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile($filePath);
        routerLog("Served static file: " . $uri);
        return true;
    }
    
    // For PHP files or unknown types, let PHP handle it
    routerLog("Letting PHP handle: " . $uri);
    return false;
}

// Route everything else to index.php
routerLog("Routing to index.php");

// Start output buffering to catch any errors
ob_start();

try {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    require __DIR__ . '/index.php';
    
    // Flush output buffer
    ob_end_flush();
    
} catch (Throwable $e) {
    // Clear output buffer on error
    ob_end_clean();
    
    routerLog("Error: " . $e->getMessage());
    routerLog("File: " . $e->getFile() . ":" . $e->getLine());
    
    http_response_code(500);
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h1>Application Error</h1>";
    echo "<p>An error occurred while processing your request.</p>";
    
    // Always show errors for debugging Railway deployment
    echo "<pre style='background:#f5f5f5;padding:20px;border:1px solid #ddd;'>";
    echo htmlspecialchars($e->getMessage()) . "\n\n";
    echo "File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "\n\n";
    echo htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
    
    echo "</body></html>";
}
