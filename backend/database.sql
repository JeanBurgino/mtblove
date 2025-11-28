-- MTB Love Datenbank Schema
-- Erstellt am: 2025-11-28

-- Datenbank erstellen
CREATE DATABASE IF NOT EXISTS `mtblove` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mtblove`;

-- Benutzer erstellen (falls noch nicht vorhanden)
-- CREATE USER IF NOT EXISTS 'mtblove_admin'@'localhost' IDENTIFIED BY 'W5vSzoCB1UniJpGZfQU9';
-- GRANT ALL PRIVILEGES ON mtblove.* TO 'mtblove_admin'@'localhost';
-- FLUSH PRIVILEGES;

-- =============================================
-- Tabelle: users (Admin-Benutzer)
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100),
    `role` ENUM('admin', 'editor') DEFAULT 'editor',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP NULL,
    INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Standard Admin-Benutzer (Passwort: admin123)
INSERT INTO `users` (`username`, `password_hash`, `email`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@mtblove.com', 'admin');

-- =============================================
-- Tabelle: wallpapers (Wallpaper & Downloads)
-- =============================================
CREATE TABLE IF NOT EXISTS `wallpapers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `style` VARCHAR(100),
    `filename` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `thumbnail_path` VARCHAR(500),
    `file_size` INT UNSIGNED,
    `width` INT UNSIGNED,
    `height` INT UNSIGNED,
    `type` ENUM('free', 'premium') DEFAULT 'free',
    `downloads` INT UNSIGNED DEFAULT 0,
    `likes` INT UNSIGNED DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_type` (`type`),
    INDEX `idx_active` (`is_active`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beispiel-Wallpapers
INSERT INTO `wallpapers` (`title`, `description`, `style`, `filename`, `file_path`, `type`) VALUES
('Skull Trail', 'Dunkle MTB Kunst mit Totenkopf-Motiv', 'Dark Art', 'skull-trail.jpg', '/uploads/wallpapers/skull-trail.jpg', 'free'),
('Neon Whip', 'Cyberpunk-inspirierte MTB Action', 'Cyberpunk', 'neon-whip.jpg', '/uploads/wallpapers/neon-whip.jpg', 'premium'),
('Love the Ride', 'Aquarell-Style MTB Illustration', 'Watercolor', 'love-ride.jpg', '/uploads/wallpapers/love-ride.jpg', 'free'),
('Flow State', 'Abstraktes MTB Kunstwerk', 'Abstract', 'flow-state.jpg', '/uploads/wallpapers/flow-state.jpg', 'premium');

-- =============================================
-- Tabelle: products (Shop-Artikel)
-- =============================================
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10, 2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'EUR',
    `image_path` VARCHAR(500),
    `category` VARCHAR(100),
    `tag` VARCHAR(50),
    `stock_quantity` INT DEFAULT 0,
    `is_available` TINYINT(1) DEFAULT 1,
    `sales_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`),
    INDEX `idx_available` (`is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beispiel-Produkte
INSERT INTO `products` (`name`, `description`, `price`, `image_path`, `category`, `tag`, `stock_quantity`) VALUES
('T-Shirt "MTB Love"', 'Hochwertiges Baumwoll-Shirt mit MTB Love Logo', 29.99, '/uploads/products/tshirt-mtb-love.jpg', 'Bekleidung', 'Bestseller', 50),
('Sticker Pack "Heartbeat"', '10 wasserfeste MTB Sticker', 9.99, '/uploads/products/sticker-heartbeat.jpg', 'Accessoires', 'New', 200),
('Poster "Trail Dreams"', 'A2 Poster auf Premium-Papier', 19.99, '/uploads/products/poster-trail-dreams.jpg', 'Druck', NULL, 30),
('Hoodie "Ride or Die"', 'Warmer Hoodie für die kalte Jahreszeit', 49.99, '/uploads/products/hoodie-ride-die.jpg', 'Bekleidung', 'New', 25);

-- =============================================
-- Tabelle: excuses (MTB Ausreden)
-- =============================================
CREATE TABLE IF NOT EXISTS `excuses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `text` TEXT NOT NULL,
    `category` VARCHAR(50),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beispiel-Ausreden
INSERT INTO `excuses` (`text`, `category`) VALUES
('Der Reifendruck war 0.05 bar zu niedrig.', 'Technik'),
('Ein Eichhörnchen hat meine Line gekreuzt.', 'Natur'),
('Die Sonne stand im falschen Winkel zur Gabel.', 'Wetter'),
('Mein Dämpfer war im Lock-Out Modus.', 'Technik'),
('Das war kein Sturz, das war ein taktischer Abstieg.', 'Humor'),
('Die Gravitation war heute besonders stark.', 'Physik'),
('Meine Reifen hatten keine Traktion auf dem Stein.', 'Technik'),
('Der Trail war viel steiler als auf Strava.', 'Navigation'),
('Ich musste einem imaginären Wanderer ausweichen.', 'Humor'),
('Die Kettenführung hat minimale Vibrationen verursacht.', 'Technik');

-- =============================================
-- Tabelle: stats (Statistiken für Admin Dashboard)
-- =============================================
CREATE TABLE IF NOT EXISTS `stats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `stat_key` VARCHAR(50) NOT NULL UNIQUE,
    `stat_value` VARCHAR(100),
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beispiel-Statistiken
INSERT INTO `stats` (`stat_key`, `stat_value`) VALUES
('total_followers', '111200'),
('total_downloads', '4800'),
('total_revenue', '842.50'),
('total_visitors', '25340');

-- =============================================
-- Tabelle: sessions (Session-Management)
-- =============================================
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Views für einfachere Abfragen
-- =============================================

-- View: Aktive Wallpapers
CREATE OR REPLACE VIEW `active_wallpapers` AS
SELECT * FROM `wallpapers` WHERE `is_active` = 1 ORDER BY `created_at` DESC;

-- View: Verfügbare Produkte
CREATE OR REPLACE VIEW `available_products` AS
SELECT * FROM `products` WHERE `is_available` = 1 ORDER BY `created_at` DESC;

-- =============================================
-- Fertig!
-- =============================================
-- Das Schema ist jetzt einsatzbereit.
-- Führe diese Datei mit: mysql -u root -p < database.sql
