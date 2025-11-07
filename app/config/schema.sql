-- ============================================================
-- SQL-Schema für mtblove.com - Instagram Analytics & Meme Gallery
-- ============================================================
-- Erstellt: 2025-11-07
-- Beschreibung: Vereinfachte Struktur für Instagram Post Analytics
-- ============================================================

-- Datenbank erstellen (falls nicht vorhanden)
CREATE DATABASE IF NOT EXISTS mtblove
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE mtblove;

-- ============================================================
-- Tabelle: users
-- Speichert Benutzerkonten für das Admin Center
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabelle: memes
-- Speichert hochgeladene Memes für die öffentliche Gallery
-- ============================================================
CREATE TABLE IF NOT EXISTS memes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_public BOOLEAN DEFAULT 1,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at),
    INDEX idx_is_public (is_public),
    INDEX idx_category (category),
    FULLTEXT INDEX idx_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabelle: instagram_posts
-- Speichert Instagram Post Analytics-Daten
-- ============================================================
CREATE TABLE IF NOT EXISTS instagram_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ig_post_id VARCHAR(100) NOT NULL UNIQUE COMMENT 'Instagram Post ID',
    image_url VARCHAR(255) NOT NULL,
    caption TEXT,
    hashtags TEXT COMMENT 'Komma-getrennte Hashtags',
    post_date DATETIME NOT NULL,
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    comments INT DEFAULT 0,
    shares INT DEFAULT 0,
    saves INT DEFAULT 0,
    engagement_rate DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Engagement-Rate in Prozent',
    imported_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ig_post_id (ig_post_id),
    INDEX idx_post_date (post_date),
    INDEX idx_imported_at (imported_at),
    INDEX idx_engagement_rate (engagement_rate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Demo-Daten einfügen
-- ============================================================

-- Demo-Benutzer erstellen
-- Passwort: admin123 (Hash generiert mit password_hash('admin123', PASSWORD_DEFAULT))
-- WICHTIG: In Produktion löschen oder Passwort ändern!
INSERT INTO users (username, email, password_hash, role, created_at) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()),
('editor', 'editor@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor', NOW()),
('viewer', 'viewer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viewer', NOW())
ON DUPLICATE KEY UPDATE id=id;

-- Demo-Memes einfügen (optional - für Tests)
INSERT INTO memes (title, image_url, description, category, created_by, is_public) VALUES
('Erstes Test-Meme', '/memes/uploads/demo1.jpg', 'Das ist ein Beispiel-Meme für die Gallery', 'Lustig', 1, 1),
('Zweites Test-Meme', '/memes/uploads/demo2.jpg', 'Noch ein lustiges Test-Meme', 'Witzig', 1, 1),
('Privates Meme', '/memes/uploads/demo3.jpg', 'Nur für eingeloggte Benutzer sichtbar', 'Intern', 1, 0)
ON DUPLICATE KEY UPDATE id=id;

-- Demo Instagram-Posts einfügen (Beispieldaten)
INSERT INTO instagram_posts (ig_post_id, image_url, caption, hashtags, post_date, views, likes, comments, shares, saves, engagement_rate) VALUES
('ABC123DEF456', 'https://example.com/post1.jpg', 'Unser erstes virales Meme! 🔥', '#meme #viral #funny #comedy', '2025-01-15 14:30:00', 15420, 1250, 89, 45, 230, 10.5),
('GHI789JKL012', 'https://example.com/post2.jpg', 'Wenn Montag kommt... 😴', '#montag #montagsmotivation #relatable', '2025-01-20 09:15:00', 8750, 680, 42, 18, 95, 9.8),
('MNO345PQR678', 'https://example.com/post3.jpg', 'Top Meme des Tages! 😂', '#meme #lol #trending #instagood', '2025-01-25 16:45:00', 22100, 1890, 156, 78, 445, 11.2),
('STU901VWX234', 'https://example.com/post4.jpg', 'Mood: Coffee ☕', '#coffee #mood #lifestyle', '2025-02-01 10:00:00', 5200, 420, 28, 12, 67, 10.1),
('YZA567BCD890', 'https://example.com/post5.jpg', 'Freitag feeling! 🎉', '#friyay #weekend #party', '2025-02-05 18:30:00', 12300, 980, 71, 34, 178, 10.3)
ON DUPLICATE KEY UPDATE id=id;

-- ============================================================
-- Views für häufige Abfragen
-- ============================================================

-- View: Top Instagram Posts (basierend auf Engagement Rate)
CREATE OR REPLACE VIEW top_instagram_posts AS
SELECT
    id,
    ig_post_id,
    caption,
    post_date,
    views,
    likes,
    comments,
    shares,
    saves,
    engagement_rate,
    (likes + comments + shares + saves) as total_engagement
FROM instagram_posts
ORDER BY engagement_rate DESC, total_engagement DESC;

-- View: Memes mit Benutzerinformationen
CREATE OR REPLACE VIEW memes_with_users AS
SELECT
    m.*,
    u.username as creator_name,
    u.email as creator_email
FROM memes m
LEFT JOIN users u ON m.created_by = u.id;

-- View: Instagram Analytics Zusammenfassung
CREATE OR REPLACE VIEW instagram_analytics_summary AS
SELECT
    COUNT(*) as total_posts,
    SUM(views) as total_views,
    SUM(likes) as total_likes,
    SUM(comments) as total_comments,
    SUM(shares) as total_shares,
    SUM(saves) as total_saves,
    AVG(engagement_rate) as avg_engagement_rate,
    MAX(engagement_rate) as max_engagement_rate,
    MIN(post_date) as first_post_date,
    MAX(post_date) as latest_post_date
FROM instagram_posts;

-- ============================================================
-- Stored Procedures
-- ============================================================

-- Procedure: Berechne Engagement Rate für einen Post
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS calculate_engagement_rate(
    IN p_post_id INT,
    OUT p_engagement_rate DECIMAL(5,2)
)
BEGIN
    DECLARE v_views INT;
    DECLARE v_total_engagement INT;

    -- Daten abrufen
    SELECT views, (likes + comments + shares + saves)
    INTO v_views, v_total_engagement
    FROM instagram_posts
    WHERE id = p_post_id;

    -- Berechne Engagement Rate (in Prozent)
    IF v_views > 0 THEN
        SET p_engagement_rate = (v_total_engagement / v_views) * 100;

        -- Update in Datenbank
        UPDATE instagram_posts
        SET engagement_rate = p_engagement_rate
        WHERE id = p_post_id;
    ELSE
        SET p_engagement_rate = 0.00;
    END IF;
END //
DELIMITER ;

-- Procedure: Update Last Login
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS update_last_login(
    IN p_user_id INT
)
BEGIN
    UPDATE users
    SET last_login = NOW()
    WHERE id = p_user_id;
END //
DELIMITER ;

-- ============================================================
-- Trigger
-- ============================================================

-- Trigger: Auto-Update Engagement Rate bei Änderung
DELIMITER //
CREATE TRIGGER IF NOT EXISTS before_instagram_post_update
BEFORE UPDATE ON instagram_posts
FOR EACH ROW
BEGIN
    DECLARE v_total_engagement INT;

    -- Berechne Gesamtinteraktionen
    SET v_total_engagement = NEW.likes + NEW.comments + NEW.shares + NEW.saves;

    -- Berechne Engagement Rate wenn Views > 0
    IF NEW.views > 0 THEN
        SET NEW.engagement_rate = (v_total_engagement / NEW.views) * 100;
    ELSE
        SET NEW.engagement_rate = 0.00;
    END IF;
END //
DELIMITER ;

-- ============================================================
-- Funktionen für Berichte
-- ============================================================

-- Funktion: Hole Top-Hashtags
DELIMITER //
CREATE FUNCTION IF NOT EXISTS get_top_hashtags(limit_count INT)
RETURNS TEXT
DETERMINISTIC
BEGIN
    DECLARE result TEXT;
    -- TODO: Implementierung für Hashtag-Analyse
    -- Extrahiert und zählt Hashtags aus allen Posts
    SET result = 'Feature in Entwicklung';
    RETURN result;
END //
DELIMITER ;

-- ============================================================
-- Hinweise zur Verwendung
-- ============================================================

-- Beispiel: Neuer Benutzer erstellen
-- INSERT INTO users (username, email, password_hash, role)
-- VALUES ('newuser', 'user@example.com', '$2y$10$...', 'editor');

-- Beispiel: Neues Meme hochladen
-- INSERT INTO memes (title, image_url, description, category, created_by)
-- VALUES ('Mein Meme', '/memes/uploads/bild.jpg', 'Beschreibung', 'Lustig', 1);

-- Beispiel: Instagram Post importieren
-- INSERT INTO instagram_posts (ig_post_id, image_url, caption, hashtags, post_date, views, likes, comments, shares, saves)
-- VALUES ('UNIQUE_ID', 'url', 'caption', '#tags', NOW(), 100, 10, 2, 1, 5);

-- Beispiel: Analytics-Zusammenfassung abrufen
-- SELECT * FROM instagram_analytics_summary;

-- Beispiel: Top Posts nach Engagement
-- SELECT * FROM top_instagram_posts LIMIT 10;

-- ============================================================
-- Ende des Schemas
-- ============================================================
