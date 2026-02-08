<?php
require_once __DIR__ . '/app/Bootstrap.php';
require_once __DIR__ . '/app/Db.php';

$pdo = \App\Db::pdo();
$users = ['admin', 'testuser'];

foreach ($users as $u) {
    $stmt = $pdo->prepare("SELECT id, username, status FROM users WHERE username = ?");
    $stmt->execute([$u]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "User: $u | Found: " . ($row ? 'Yes' : 'No') . " | Status: " . ($row['status'] ?? 'N/A') . "\n";
}
