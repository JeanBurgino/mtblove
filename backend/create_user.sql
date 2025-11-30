-- =============================================
-- MTB Love - User Creation SQL Script
-- =============================================
-- This script creates new users for API access
--
-- USAGE:
-- 1. Replace the values in the INSERT statement below
-- 2. Run: mysql -u mtblove_admin -p mtblove < backend/create_user.sql
--
-- OR execute directly in MySQL:
-- mysql -u mtblove_admin -p mtblove
-- source backend/create_user.sql;
-- =============================================

USE mtblove;

-- =============================================
-- EXAMPLE 1: Create API Admin User
-- =============================================
-- Username: api_admin
-- Password: ApiAdmin123
-- Role: admin
INSERT INTO users (username, password_hash, email, role, created_at)
VALUES (
    'api_admin',
    '$2y$12$ONJfikHadiI.1II6G2TZn.Rd1/mTFY4eofOwPWlyV/8w0cvaUC1Su',
    'api_admin@mtblove.com',
    'admin',
    NOW()
)
ON DUPLICATE KEY UPDATE
    username = username; -- Skip if user already exists

-- =============================================
-- EXAMPLE 2: Create API Editor User
-- =============================================
-- Username: api_editor
-- Password: ApiEditor123
-- Role: editor
INSERT INTO users (username, password_hash, email, role, created_at)
VALUES (
    'api_editor',
    '$2y$12$B3oRAiDD5Ku.GrNmO5A2mOHT9WtTLSId9SmQeZYCQchye3PVFsoXm',
    'api_editor@mtblove.com',
    'editor',
    NOW()
)
ON DUPLICATE KEY UPDATE
    username = username; -- Skip if user already exists

-- =============================================
-- TEMPLATE: Add Your Own User
-- =============================================
-- Copy and modify this template to create your own users
-- IMPORTANT: Generate password hash using PHP:
--   php -r "echo password_hash('YourPassword', PASSWORD_BCRYPT);"

-- INSERT INTO users (username, password_hash, email, role, created_at)
-- VALUES (
--     'your_username',           -- Replace with desired username
--     'YOUR_PASSWORD_HASH_HERE', -- Replace with bcrypt hash from PHP
--     'your_email@example.com',  -- Replace with email
--     'admin',                   -- 'admin' or 'editor'
--     NOW()
-- );

-- =============================================
-- VERIFICATION: Check Created Users
-- =============================================
SELECT
    id,
    username,
    email,
    role,
    created_at,
    last_login
FROM users
ORDER BY created_at DESC;

-- =============================================
-- NOTES & HOW TO USE
-- =============================================

-- The examples above (api_admin and api_editor) have REAL, working password hashes.
-- You can use them immediately or create your own users using the methods below.

-- =============================================
-- PASSWORD HASHING METHODS
-- =============================================

-- Method 1 - Using generate_password_hash.php (EASIEST):
--   php backend/generate_password_hash.php YourPassword123
--   This outputs a ready-to-use SQL INSERT statement!

-- Method 2 - Using create_user.php script (RECOMMENDED):
--   php backend/create_user.php username password role
--   This creates the user directly in the database

-- Method 3 - Using PHP CLI:
--   php -r "echo password_hash('YourPassword123', PASSWORD_BCRYPT) . PHP_EOL;"

-- =============================================
-- QUICK START
-- =============================================

-- 1. Run this SQL file to create example users:
--    mysql -u mtblove_admin -p mtblove < backend/create_user.sql

-- 2. Test login with api_admin:
--    curl -X POST http://localhost/backend/api/index.php \
--      -d "action=login" \
--      -d "username=api_admin" \
--      -d "password=ApiAdmin123"

-- 3. Use the returned token for authenticated API calls!

-- =============================================
-- SECURITY
-- =============================================
-- - Passwords must be at least 8 characters
-- - PASSWORD_BCRYPT algorithm is used for hashing
-- - Never store plain text passwords
-- - Change default passwords in production!
-- - Use strong passwords with numbers, symbols, and mixed case

-- =============================================
-- ROLES
-- =============================================
-- - 'admin': Full access to all API endpoints
-- - 'editor': Limited access (can be customized in auth.php)

-- =============================================
-- GENERATED CREDENTIALS
-- =============================================
-- api_admin / ApiAdmin123
-- api_editor / ApiEditor123
-- admin / admin123 (default, already exists from database.sql)
