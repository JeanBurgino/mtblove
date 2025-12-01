-- =============================================
-- MTB Love - Create Sessions Table
-- =============================================
-- This script creates the missing sessions table
-- needed for login functionality
-- =============================================

USE mtblove;

-- Create sessions table (without foreign key for compatibility)
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_token` (`token`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Add foreign key constraint if users table exists with correct structure
-- Uncomment the following lines if you want to add the foreign key later:
-- ALTER TABLE sessions
-- ADD CONSTRAINT fk_sessions_user_id
-- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Verify table was created
DESCRIBE sessions;

-- Show all tables
SHOW TABLES;

-- =============================================
-- USAGE
-- =============================================
-- Run with: mysql -u mtblove_admin -p mtblove < backend/create_sessions_table.sql
--
-- =============================================
-- NOTES
-- =============================================
-- This table is required for:
-- - Storing authentication tokens
-- - Session management
-- - User login functionality
