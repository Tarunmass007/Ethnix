<?php
// api/internal_runner.php
// Helper to run legitimate gate scripts via simulated isolation (HTTP curl from webhook)
// This allows the webhook to run multiple checks in a loop (for /mass) without searching for 'exit' in every file.

declare(strict_types = 1)
;

require_once __DIR__ . '/../app/Bootstrap.php';
require_once __DIR__ . '/../app/Settings.php';
require_once __DIR__ . '/../app/Db.php';

// Security: Only allow local requests or requests with a secret
// In a real app, use a strong shared secret. For now, we trust localhost/deployment env.
// $isLocal = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1');

// 1. Inputs
$uid = (int)($_GET['uid'] ?? 0);
$file = $_GET['file'] ?? '';
$cc = $_GET['cc'] ?? '';
// Hitter-specific
$url = $_GET['url'] ?? '';

if (!$uid || !$file || !$cc) {
    echo "Missing params";
    exit;
}

// 2. Validate File
// Only allow files within api/ directory
$realPath = realpath($file);
if (!$realPath || !str_starts_with($realPath, __DIR__)) {
    echo "Invalid file";
    exit;
}

// 3. Setup Environment for the included script
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['uid'] = $uid;
// We might need to fetch username to populate $_SESSION['uname'] if the script relies on it
// But most scripts fetch user data from DB using uid.
$_SESSION['uname'] = 'tg_user';


// 4. Setup GET params expected by the script
$_GET['cc'] = $cc;
$_GET['siteLink'] = ''; // Default
$_GET['hitSender'] = 'both';
if ($url) {
    $_GET['url'] = $url;
}

// 5. Run the script
// We rely on the script's own output (echo json_encode...) and Telegram sending.
// We capture output just to be clean, but we don't return it to webhook necessarily, 
// as the script sends its own Telegram message.
ob_start();
try {
    include $realPath;
}
catch (\Throwable $e) {
    error_log("Runner Error: " . $e->getMessage());
}
ob_end_clean();
