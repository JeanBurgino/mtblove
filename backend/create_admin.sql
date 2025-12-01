-- =============================================
-- MTB Love - Create Admin User
-- =============================================
-- Quick script to create an admin user
-- Password: admin123 (CHANGE THIS IN PRODUCTION!)
-- =============================================

USE mtblove;

-- Create admin user
-- Username: admin
-- Password: admin123
-- Email: admin@mtblove.com
INSERT INTO users (username, password_hash, email, role, created_at)
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin@mtblove.com',
    'admin',
    NOW()
)
ON DUPLICATE KEY UPDATE
    password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    email = 'admin@mtblove.com',
    role = 'admin';

-- Show created user
SELECT
    id,
    username,
    email,
    role,
    created_at
FROM users
WHERE username = 'admin';

-- =============================================
-- USAGE
-- =============================================
-- Run with: mysql -u mtblove_admin -p mtblove < backend/create_admin.sql
--
-- Or execute in MySQL:
-- mysql -u mtblove_admin -p mtblove
-- source backend/create_admin.sql;
--
-- =============================================
-- CREDENTIALS
-- =============================================
-- Username: admin
-- Password: admin123
--
-- =============================================
-- CHANGE PASSWORD
-- =============================================
-- To use a different password, generate a new hash:
--   php backend/generate_password_hash.php YourNewPassword
--
-- Then replace the password_hash in this file with the generated hash
--
-- =============================================
-- TEST LOGIN
-- =============================================
-- curl -X POST http://localhost/backend/api/index.php \
--   -d "action=login" \
--   -d "username=admin" \
--   -d "password=admin123"
