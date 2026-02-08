<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Bootstrap.php';
require_once __DIR__ . '/../app/Db.php';

use App\Db;

header('Content-Type: application/json');

// 1. Auth check
if (empty($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$uid = (int)$_SESSION['uid'];

// 2. File Check
if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok' => false, 'error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['avatar'];
$maxSize = 2 * 1024 * 1024; // 2MB

if ($file['size'] > $maxSize) {
    echo json_encode(['ok' => false, 'error' => 'File too large (Max 2MB)']);
    exit;
}

// 3. Validate Type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

if (!in_array($mime, $allowed, true)) {
    echo json_encode(['ok' => false, 'error' => 'Invalid file type. Only JPG, PNG, WEBP, GIF allowed.']);
    exit;
}

// 4. Move File
// Ensure directory exists
$uploadDir = __DIR__ . '/../assets/profiles/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique name: profile_{uid}_{timestamp}.ext
$ext = match($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
    default => 'jpg'
};
$filename = "profile_{$uid}_" . time() . ".{$ext}";
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['ok' => false, 'error' => 'Failed to save file']);
    exit;
}

// 5. Update DB
$publicUrl = "/assets/profiles/{$filename}";
$pdo = Db::pdo();

try {
    $stmt = $pdo->prepare("UPDATE users SET profile_picture = :p WHERE id = :id");
    $stmt->execute([':p' => $publicUrl, ':id' => $uid]);
    
    // Update session immediately for faster UI reflection (optional)
    // $_SESSION['user_pic'] = $publicUrl; // if you store it in session

    echo json_encode(['ok' => true, 'url' => $publicUrl]);
} catch (Exception $e) {
    error_log("Profile upload db error: ".$e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Database update failed']);
}
