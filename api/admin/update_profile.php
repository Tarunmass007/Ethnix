<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/Bootstrap.php';
require_once __DIR__ . '/../../app/Db.php';

use App\Db;

header('Content-Type: application/json');

/* Auth check */
if (empty($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$pdo = Db::pdo();
$uid = (int)$_SESSION['uid'];

/* Verify Admin Status */
$stmt = $pdo->prepare("SELECT id, status, password_hash FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['status'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

/* Handle POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    $updates = [];
    $params = [':id' => $uid];

    // Username update
    if (!empty($newUsername)) {
        // Check uniqueness if changed
        $check = $pdo->prepare("SELECT id FROM users WHERE username = :u AND id != :id LIMIT 1");
        $check->execute([':u' => $newUsername, ':id' => $uid]);
        if ($check->fetchColumn()) {
            echo json_encode(['ok' => false, 'error' => 'Username already taken']);
            exit;
        }
        $updates[] = "username = :u";
        $params[':u'] = $newUsername;
    }

    // Password update
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            echo json_encode(['ok' => false, 'error' => 'Password must be at least 6 chars']);
            exit;
        }
        // Hash the password
        $updates[] = "password_hash = :p";
        $params[':p'] = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    if (empty($updates)) {
        echo json_encode(['ok' => true, 'msg' => 'No changes made']);
        exit;
    }

    try {
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
        $pdo->prepare($sql)->execute($params);
        
        // If username changed, update session
        if (!empty($newUsername)) {
            $_SESSION['uname'] = $newUsername;
        }

        echo json_encode(['ok' => true, 'msg' => 'Profile updated successfully']);
    } catch (PDOException $e) {
        error_log("Update profile error: " . $e->getMessage());
        echo json_encode(['ok' => false, 'error' => 'Database error']);
    }
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Invalid method']);
