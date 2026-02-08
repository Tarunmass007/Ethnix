<?php
declare(strict_types = 1)
;

require_once __DIR__ . '/../app/Bootstrap.php';
require_once __DIR__ . '/../app/Settings.php';
require_once __DIR__ . '/../app/Telegram.php';
require_once __DIR__ . '/../app/Db.php';

// 1. Load Settings (env vars)
try {
    App\Settings::load();
}
catch (\Throwable $e) {
    error_log('Webhook settings load failed: ' . $e->getMessage());
    http_response_code(500);
    exit;
}

$botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? '';
if (empty($botToken)) {
    http_response_code(500);
    exit;
}

// 2. Read incoming update
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update || !isset($update['message']['text'])) {
    echo 'ok';
    exit;
}

// 3. Extract Info
$message = $update['message'];
$chatId = $message['chat']['id'];
$text = trim($message['text']);
$userId = $message['from']['id'];
$username = $message['from']['username'] ?? '';
$fname = $message['from']['first_name'] ?? '';
$lname = $message['from']['last_name'] ?? '';

// 4. Authenticate / Sync User
// We need the internal User ID (uid) for the gates to work (credits, etc.)
$pdo = App\Db::pdo();
$userRes = App\Telegram::saveUser($pdo, (string)$userId, $username, $fname, $lname);

if (!$userRes['ok']) {
    App\Telegram::sendMessage($botToken, (string)$chatId, "⚠️ Database Error: Could not verify user.");
    exit;
}

$internalUid = $userRes['id'];

// 5. Setup Mock Session/Env for Gates
// The gates rely on $_SESSION['uid'] and $_GET params.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['uid'] = $internalUid;
$_SESSION['uname'] = $username ?: "tg_{$internalUid}";

// 6. Parse Command
// Pattern: /command args
$parts = explode(' ', $text, 2);
$cmd = strtolower($parts[0]);
$args = isset($parts[1]) ? trim($parts[1]) : '';

// 7. Command Dispatch
// We map commands to API files.
$gates = [
    '/chk' => __DIR__ . '/stripe/autowoostripe1.php',
    '/sk' => __DIR__ . '/stripe/skbased.php',
    '/pp' => __DIR__ . '/paypal/paypalcharge.php',
    '/br' => __DIR__ . '/braintree/b3charge.php',
    '/shop' => __DIR__ . '/shopify/autoshopify.php',
    '/kill' => __DIR__ . '/killer/api.php',
    // Hitter
    '/hitter' => __DIR__ . '/autohitter/checkouthitter.php',
    // Aliases
    '/stripe' => __DIR__ . '/stripe/autowoostripe1.php',
    '/paypal' => __DIR__ . '/paypal/paypalcharge.php',
    '/hit' => __DIR__ . '/autohitter/checkouthitter.php',
];

// Helper to run a gate via internal runner (curl)
// This is used for mass checks to avoid 'exit' killing the loop.
$runGate = function ($gateFile, $cc, $extraParams = []) use ($internalUid, $botToken, $chatId) {
    // Current host URL
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $runnerUrl = "{$protocol}://{$host}/api/internal_runner.php";

    $params = [
        'uid' => $internalUid,
        'file' => $gateFile,
        'cc' => $cc,
    ];
    $params = array_merge($params, $extraParams);

    // Fire and forget (ish) - we wait 1s timeout? 
    // No, we want the script to run. The script sends the Telegram msg.
    // We just trigger it.

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $runnerUrl . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Short timeout, let backend process
    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
    curl_exec($ch);
    curl_close($ch);
};

if ($cmd === '/mass') {
    // Usage: /mass chk cc|... cc|...
    $lines = explode("\n", $args);
    $subCmd = strtolower(trim($lines[0] ?? ''));

    // Check if sub command is valid (e.g. 'chk')
    // We accept '/chk' or 'chk'
    $targetGateKey = str_starts_with($subCmd, '/') ? $subCmd : '/' . $subCmd;

    if (!isset($gates[$targetGateKey])) {
        App\Telegram::sendMessage($botToken, (string)$chatId, "⚠️ Invalid Mass Command. Usage:\n/mass chk\ncc|mm|yy|cvv\n...");
        exit;
    }

    $gateFile = $gates[$targetGateKey];
    $cards = array_slice($lines, 1); // Rest of lines
    $cards = array_filter(array_map('trim', $cards));

    if (count($cards) > 20) {
        App\Telegram::sendMessage($botToken, (string)$chatId, "⚠️ Batch limited to 20 cards.");
        exit;
    }

    if (empty($cards)) {
        App\Telegram::sendMessage($botToken, (string)$chatId, "⚠️ No cards found.");
        exit;
    }

    App\Telegram::sendMessage($botToken, (string)$chatId, "🚀 <b>Mass Check Started</b> (" . count($cards) . " cards)", 'HTML');

    foreach ($cards as $cc) {
        if (preg_match('/\d{15,16}/', $cc)) {
            $runGate($gateFile, $cc);
            sleep(1); // Small delay to prevent flood
        }
    }
    exit;
}

if (isset($gates[$cmd])) {
    $targetFile = $gates[$cmd];

    if (!file_exists($targetFile)) {
        App\Telegram::sendMessage($botToken, (string)$chatId, "⚠️ Error: Gate file not found for {$cmd}");
        exit;
    }

    if (empty($args)) {
        $usage = ($cmd === '/hitter' || $cmd === '/hit')
            ? "{$cmd} url cc|mm|yy|cvv"
            : "{$cmd} cc|mm|yy|cvv";
        App\Telegram::sendMessage($botToken, (string)$chatId, "⚠️ Usage: {$usage}");
        exit;
    }

    // Hitter Special Parsing
    $extra = [];
    $ccToUse = $args;

    if ($cmd === '/hitter' || $cmd === '/hit') {
        // args: <url> <cc>
        $parts = preg_split('/\s+/', $args, 2);
        if (count($parts) < 2) {
            App\Telegram::sendMessage($botToken, (string)$chatId, "⚠️ Usage: {$cmd} <url> <cc>");
            exit;
        }
        $url = $parts[0];
        $ccToUse = $parts[1];
        $extra['url'] = $url;
    }

    // Processing
    App\Telegram::sendMessage($botToken, (string)$chatId, "⏳ <b>Processing...</b>", 'HTML');

    // For single commands, we CAN use include directly for speed/simplicity, 
    // BUT 'checkouthitter' relies on $_GET['url'].
    // To keep it unified and simple, we can just use the Runner for everything now.
    // This solves the 'exit' problem consistently.

    $runGate($targetFile, $ccToUse, $extra);
    exit;
}

// 8. System Commands
if ($cmd === '/start' || $cmd === '/help') {
    $msg = "<b>Welcome to BabaChecker Bot!</b> \u{1F44B}\n\n" .
        "<b>Available Commands:</b>\n\n" .
        "<b>Single Card Checks:</b>\n" .
        "<code>/chk cc|mm|yy|cvv</code> - Stripe Charge\n" .
        "<code>/sk cc|mm|yy|cvv</code>  - SK Based\n" .
        "<code>/pp cc|mm|yy|cvv</code>  - PayPal Charge\n" .
        "<code>/br cc|mm|yy|cvv</code>  - Braintree\n" .
        "<code>/shop cc|mm|yy|cvv</code> - Shopify\n" .
        "<code>/kill cc|mm|yy|cvv</code> - Card Killer\n\n" .
        "<b>Hitter:</b>\n" .
        "<code>/hitter <url> <cc></code> - PayCheckout Hitter\n\n" .
        "<b>Mass Checker:</b>\n" .
        "<code>/mass <cmd> <list></code> - Check up to 20 cards\n" .
        "Example: \n<code>/mass chk\n411111|...\n511111|...</code>\n\n" .
        "<b>Account:</b>\n" .
        "<code>/id</code> - Get your Telegram ID";
    App\Telegram::sendMessage($botToken, (string)$chatId, $msg);
}
elseif ($cmd === '/id') {
    App\Telegram::sendMessage($botToken, (string)$chatId, "Your ID is: <code>{$chatId}</code>");
}

echo 'ok';
