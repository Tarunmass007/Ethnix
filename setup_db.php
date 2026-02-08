<?php
// setup_db.php
// Run this to create the database and tables for local development.

declare(strict_types=1);

require_once __DIR__ . '/app/Bootstrap.php';

echo "Setting up database...\n";

// Get database credentials from environment or use local defaults
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? ''; // Default XAMPP password
$dbName = $_ENV['DB_NAME'] ?? 'babachecker';

echo "Connecting to MySQL at {$host}:{$port}...\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbName' checked/created.\n";

    // Select Database
    $pdo->exec("USE `$dbName`");

    // Create Users Table
    $sqlUsers = "
    CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `telegram_id` bigint(20) DEFAULT NULL,
        `username` varchar(255) DEFAULT NULL,
        `first_name` varchar(255) DEFAULT NULL,
        `last_name` varchar(255) DEFAULT NULL,
        `profile_picture` text DEFAULT NULL,
        `email` varchar(255) DEFAULT NULL,
        `password_hash` varchar(255) DEFAULT NULL,
        `otp_secret` varchar(255) DEFAULT NULL,
        `is_active` tinyint(1) DEFAULT 1,
        `status` varchar(50) DEFAULT 'free',
        `previous_status` varchar(50) DEFAULT NULL,
        `plan_name` varchar(50) DEFAULT NULL,
        `credits` int(11) DEFAULT 0,
        `lives` int(11) DEFAULT 0,
        `charges` int(11) DEFAULT 0,
        `xcoin` int(11) DEFAULT 0 COMMENT 'Previously kcoin in logic',
        `kcoin` int(11) DEFAULT 0,
        `theme_preference` varchar(50) DEFAULT 'dark',
        `online_status` varchar(50) DEFAULT 'offline',
        `expiry_date` datetime DEFAULT NULL,
        `last_login` datetime DEFAULT NULL,
        `last_activity` datetime DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_telegram_id` (`telegram_id`),
        KEY `index_username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sqlUsers);
    echo "Table 'users' checked/created.\n";

    // Create User Proxies Table
    $sqlProxies = "
    CREATE TABLE IF NOT EXISTS `user_proxies` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `name` varchar(255) DEFAULT NULL,
        `host` varchar(255) NOT NULL,
        `port` int(11) NOT NULL,
        `username` varchar(255) DEFAULT NULL,
        `password` varchar(255) DEFAULT NULL,
        `ptype` varchar(10) DEFAULT 'http',
        `status` varchar(20) DEFAULT 'active',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_user_proxy` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sqlProxies);
    echo "Table 'user_proxies' checked/created.\n";

    // Create Settings Table
    $sqlSettings = "
    CREATE TABLE IF NOT EXISTS `settings` (
        `key` varchar(64) NOT NULL,
        `val` text DEFAULT NULL,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sqlSettings);
    echo "Table 'settings' checked/created.\n";

    // Create Redeem Codes Table
    $sqlRedeem = "
    CREATE TABLE IF NOT EXISTS `redeemcodes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `code` varchar(50) NOT NULL,
        `status` varchar(20) NOT NULL DEFAULT 'FREE',
        `credits` int(11) DEFAULT 0,
        `expiry_date` date DEFAULT NULL,
        `isRedeemed` tinyint(1) DEFAULT 0,
        `redeemed_by` int(11) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_code` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sqlRedeem);
    echo "Table 'redeemcodes' checked/created.\n";

    // Insert Admin User if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("
            INSERT INTO users (telegram_id, username, first_name, status, credits, xcoin, kcoin, is_active) 
            VALUES (123456789, 'admin', 'Local Admin', 'admin', 99999, 1000, 1000, 1)
        ");
        $insert->execute();
        echo "Admin user created (username: admin).\n";
    } else {
        echo "Admin user already exists.\n";
    }

    // Insert Test User (Free tier) if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'testuser'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("
            INSERT INTO users (telegram_id, username, first_name, status, credits, xcoin, kcoin, is_active) 
            VALUES (987654321, 'testuser', 'Test User', 'free', 500, 10, 10, 1)
        ");
        $insert->execute();
        echo "Test user created (username: testuser).\n";
    } else {
        echo "Test user already exists.\n";
    }

} catch (PDOException $e) {
    die("DB Setup Error: " . $e->getMessage() . "\n");
}
