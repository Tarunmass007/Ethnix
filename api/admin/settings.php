<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Bootstrap.php';
require_once __DIR__ . '/../../app/Db.php';

use App\Db;

header('Content-Type: application/json');

// Auth check
if (empty($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$pdo = Db::pdo();
$uid = (int)$_SESSION['uid'];

// Check admin
$stmt = $pdo->prepare("SELECT status FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $uid]);
$u = $stmt->fetch();

if (!$u || $u['status'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

// Handle POST (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Whitelist allowed keys to prevent polluting DB with random junk
    $allowed = [
        'TELEGRAM_BOT_TOKEN', 'TELEGRAM_BOT_USERNAME', 'TELEGRAM_ADMIN_USERNAME', 
        'TELEGRAM_ANNOUNCE_CHAT_ID', 'TELEGRAM_ALLOWED_IDS',
        'PAYMENT_BINANCE_ID', 'PAYMENT_BTC_ADDR', 'PAYMENT_LTC_ADDR', 'PAYMENT_TRX_ADDR',
        'PAYMENT_USDT_TRC20', 'PAYMENT_USDT_BEP20',
        'PAYMENT_UPI_ID', 'PAYMENT_QR_LINK'
    ];

    $updates = 0;
    foreach ($allowed as $key) {
        if (isset($_POST[$key])) {
            $val = trim($_POST[$key]);
            // Insert or Update
            $sql = "INSERT INTO settings (`key`, `val`) VALUES (:key, :val) 
                    ON DUPLICATE KEY UPDATE `val` = VALUES(`val`)";
            $pdo->prepare($sql)->execute([':key' => $key, ':val' => $val]);
            $updates++;
        }
    }

    echo json_encode(['ok' => true, 'msg' => "Updated $updates settings."]);
    exit;
}

// Handle GET (Fetch)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT `key`, `val` FROM settings");
    $all = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [key => val]

    // We also want to return current values from $_ENV if not in DB yet (for initial population)
    // accessible keys
    $keys = [
        'TELEGRAM_BOT_TOKEN', 'TELEGRAM_BOT_USERNAME', 'TELEGRAM_ADMIN_USERNAME', 
        'TELEGRAM_ANNOUNCE_CHAT_ID', 'TELEGRAM_ALLOWED_IDS',
        'PAYMENT_BINANCE_ID', 'PAYMENT_BTC_ADDR', 'PAYMENT_LTC_ADDR', 'PAYMENT_TRX_ADDR',
        'PAYMENT_USDT_TRC20', 'PAYMENT_USDT_BEP20',
        'PAYMENT_UPI_ID', 'PAYMENT_QR_LINK'
    ];

    $data = [];
    foreach ($keys as $k) {
        // DB value takes precedence, then ENV, then empty
        $data[$k] = $all[$k] ?? ($_ENV[$k] ?? '');
    }

    echo json_encode(['ok' => true, 'data' => $data]);
    exit;
}
