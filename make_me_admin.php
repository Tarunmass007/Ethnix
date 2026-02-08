<?php
require_once __DIR__ . '/app/Bootstrap.php';
require_once __DIR__ . '/app/Db.php';

$secretKey = 'baba_secret_123'; // Simple protection

$user = $_GET['user'] ?? '';
$key = $_GET['key'] ?? '';

if ($key !== $secretKey) {
    die("❌ Access Denied. Wrong key.");
}

if (empty($user)) {
    die("❌ Please provide a username. Example: ?user=MyTelegramUser&key=baba_secret_123");
}

try {
    $pdo = App\Db::pdo();
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, status FROM users WHERE username = :u");
    $stmt->execute([':u' => $user]);
    $u = $stmt->fetch();
    
    if (!$u) {
        die("❌ User '{$user}' not found in database. Please login firmly first!");
    }
    
    // Update to admin
    $upd = $pdo->prepare("UPDATE users SET status='admin', credits=99999 WHERE id = :id");
    $upd->execute([':id' => $u['id']]);
    
    echo "✅ Success! User '{$user}' is now an ADMIN (and has 99,999 credits).<br>";
    echo "<a href='/app/dashboard'>Go to Dashboard</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
