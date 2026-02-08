<?php
// server_router.php
// Router script for PHP built-in server to mimic Nginx rewrites

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files if they exist
if (file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// Handle /app/ routes
if (str_starts_with($uri, '/app/')) {
    // Mimic Nginx: /app/<path> -> /router.php?path=<path>
    $_GET['path'] = substr($uri, 5); // remove /app/
    require __DIR__ . '/router.php';
    return;
}

// Handle root
if ($uri === '/') {
    require __DIR__ . '/index.php';
    return;
}

// Default 404
http_response_code(404);
echo "404 Not Found (Local Router)";
