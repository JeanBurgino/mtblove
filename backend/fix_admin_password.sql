-- =============================================
-- MTB Love - Fix Admin Password
-- =============================================
-- This script resets the admin password to: admin123
-- =============================================

USE mtblove;

-- Update admin user password
-- Password: admin123
-- Hash generated and verified by test_login.php
UPDATE users
SET password_hash = '$2y$12$pPRZl.9pbOtN/J0V.gXzh.wd6iMEgPrdTZNQV0UmFlBtUJCGidS4S'
WHERE username = 'admin';

-- Verify the update
SELECT
    id,
    username,
    email,
    role,
    created_at,
    last_login
FROM users
WHERE username = 'admin';

-- =============================================
-- USAGE
-- =============================================
-- Run with: mysql -u mtblove_admin -p mtblove < backend/fix_admin_password.sql
--
-- =============================================
-- NEW CREDENTIALS
-- =============================================
-- Username: admin
-- Password: admin123
--
-- =============================================
-- TEST LOGIN
-- =============================================
-- curl -X POST https://mtblove.com/backend/api/index.php \
--   -d "action=login" \
--   -d "user=admin" \
--   -d "pass=admin123"
