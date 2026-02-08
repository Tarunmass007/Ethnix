-- BabaChecker Database Schema
-- Run this on Railway MySQL to create all tables

-- Select the database (replace 'railway' with your actual database name)
USE railway;

-- Create Users Table
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

-- Create User Proxies Table
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

-- Create Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
    `key` varchar(64) NOT NULL,
    `val` text DEFAULT NULL,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Redeem Codes Table
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

-- Insert Admin User (if not exists)
INSERT IGNORE INTO users (telegram_id, username, first_name, status, credits, xcoin, kcoin, is_active) 
VALUES (123456789, 'admin', 'Local Admin', 'admin', 99999, 1000, 1000, 1);

-- Insert Test User (if not exists)
INSERT IGNORE INTO users (telegram_id, username, first_name, status, credits, xcoin, kcoin, is_active) 
VALUES (987654321, 'testuser', 'Test User', 'free', 500, 10, 10, 1);
