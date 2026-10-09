-- =====================================================================
-- Ceylon Focus News - Database Schema
-- Laravel 11 + MySQL 8
-- =====================================================================
-- This file mirrors what `php artisan migrate` will create. It is
-- provided for reference / manual import if ever needed, but running
-- the Laravel migrations is the recommended approach (see README).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS news_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE news_portal;

-- ---------------------------------------------------------------------
-- users  (Laravel default table + role column)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- categories
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- news
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `news` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `image` VARCHAR(255) NULL,
  `excerpt` TEXT NULL,
  `content` LONGTEXT NOT NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('draft','published') NOT NULL DEFAULT 'published',
  `views` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `published_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  KEY `news_status_published_at_index` (`status`, `published_at`),
  CONSTRAINT `news_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `news_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- advertisements
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `advertisements` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `url` VARCHAR(255) NULL,
  `position` ENUM('header','sidebar','article_top','article_bottom','footer') NOT NULL DEFAULT 'sidebar',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- videos  (fixed 4 homepage slots via UNIQUE(position))
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `videos` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `embed_url` VARCHAR(255) NULL,
  `platform` ENUM('youtube','facebook') NOT NULL DEFAULT 'youtube',
  `position` TINYINT UNSIGNED NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `videos_position_unique` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Seed data: default admin account
-- Email: admin@example.com   Password: Admin@12345  (CHANGE AFTER LOGIN)
-- The hash below is a real bcrypt hash of "Admin@12345" (cost 12) and
-- is fully compatible with Laravel's Hash::check()/password_verify().
-- Recommended instead: run `php artisan db:seed` which uses
-- Hash::make() directly — see AdminUserSeeder.php.
-- ---------------------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `role`, `password`, `created_at`, `updated_at`)
VALUES ('Site Administrator', 'admin@example.com', 'admin',
        '$2b$12$FCd9YlLrHhkozIEr8x0cgu2ae51k27226YV.NzWCQOsAs9VatSqZ6', NOW(), NOW())
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO `categories` (`name`, `slug`, `created_at`, `updated_at`) VALUES
('National', 'national', NOW(), NOW()),
('World', 'world', NOW(), NOW()),
('Politics', 'politics', NOW(), NOW()),
('Business', 'business', NOW(), NOW()),
('Sport', 'sport', NOW(), NOW()),
('Technology', 'technology', NOW(), NOW()),
('Entertainment', 'entertainment', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = name;
