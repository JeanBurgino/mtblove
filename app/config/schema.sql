-- ============================================================
-- SQL-Schema für Meme Gallery Anwendung
-- ============================================================
-- Erstellt: 2025-11-07
-- Beschreibung: Datenbankstruktur für Benutzer, Memes und Analytics
-- ============================================================

-- Datenbank erstellen (falls nicht vorhanden)
CREATE DATABASE IF NOT EXISTS meme_gallery
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE meme_gallery;

-- ============================================================
-- Tabelle: users
-- Speichert Benutzerkonten für das Admin-Dashboard
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    active TINYINT(1) DEFAULT 1,
    role ENUM('admin', 'moderator', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo-Benutzer erstellen (Passwort: admin123)
-- TODO: In Produktion entfernen oder sicheres Passwort verwenden!
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- ============================================================
-- Tabelle: memes
-- Speichert alle hochgeladenen Memes
-- ============================================================
CREATE TABLE IF NOT EXISTS memes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) DEFAULT NULL,
    caption TEXT DEFAULT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    width INT UNSIGNED DEFAULT NULL,
    height INT UNSIGNED DEFAULT NULL,
    tags VARCHAR(500) DEFAULT NULL COMMENT 'Komma-getrennte Tags',
    views INT UNSIGNED DEFAULT 0,
    likes INT UNSIGNED DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_views (views),
    INDEX idx_likes (likes),
    INDEX idx_active (active),
    INDEX idx_featured (featured),
    FULLTEXT INDEX idx_search (title, caption, tags)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabelle: meme_views (für Analytics)
-- TODO: Tracking von einzelnen Views für detaillierte Statistiken
-- ============================================================
CREATE TABLE IF NOT EXISTS meme_views (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meme_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    referer VARCHAR(500) DEFAULT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
    INDEX idx_meme_id (meme_id),
    INDEX idx_viewed_at (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabelle: meme_likes (für Like-System)
-- TODO: Implementierung eines Like/Favorite-Systems
-- ============================================================
CREATE TABLE IF NOT EXISTS meme_likes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meme_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    liked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (meme_id, user_id),
    INDEX idx_meme_id (meme_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabelle: comments (für Kommentar-System)
-- TODO: Kommentarfunktion für Memes implementieren
-- ============================================================
CREATE TABLE IF NOT EXISTS comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meme_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    username VARCHAR(50) DEFAULT NULL COMMENT 'Für Gast-Kommentare',
    comment TEXT NOT NULL,
    approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_meme_id (meme_id),
    INDEX idx_approved (approved),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabelle: tags (normalisierte Tag-Tabelle)
-- TODO: Separate Tag-Verwaltung für bessere Organisation
-- ============================================================
CREATE TABLE IF NOT EXISTS tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    usage_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tag_name (tag_name),
    INDEX idx_usage_count (usage_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabelle: meme_tags (Many-to-Many Beziehung)
-- TODO: Verknüpfung zwischen Memes und Tags
-- ============================================================
CREATE TABLE IF NOT EXISTS meme_tags (
    meme_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (meme_id, tag_id),
    FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    INDEX idx_meme_id (meme_id),
    INDEX idx_tag_id (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabelle: sessions (optionales Session-Management)
-- TODO: DB-basierte Session-Verwaltung für bessere Sicherheit
-- ============================================================
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    payload TEXT NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabelle: analytics_events (für Custom Analytics)
-- TODO: Eigenes Analytics-System für detaillierte Auswertungen
-- ============================================================
CREATE TABLE IF NOT EXISTS analytics_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    event_data JSON DEFAULT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    referer VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_event_type (event_type),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Views für häufige Abfragen
-- ============================================================

-- View: Trending Memes (basierend auf Views der letzten 7 Tage)
CREATE OR REPLACE VIEW trending_memes AS
SELECT
    m.*,
    u.username as uploader,
    COUNT(v.id) as recent_views
FROM memes m
LEFT JOIN users u ON m.user_id = u.id
LEFT JOIN meme_views v ON m.id = v.meme_id AND v.viewed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
WHERE m.active = 1
GROUP BY m.id
ORDER BY recent_views DESC, m.created_at DESC;

-- View: Popular Memes (basierend auf Likes)
CREATE OR REPLACE VIEW popular_memes AS
SELECT
    m.*,
    u.username as uploader,
    COUNT(l.id) as like_count
FROM memes m
LEFT JOIN users u ON m.user_id = u.id
LEFT JOIN meme_likes l ON m.id = l.meme_id
WHERE m.active = 1
GROUP BY m.id
ORDER BY like_count DESC, m.created_at DESC;

-- ============================================================
-- Stored Procedures (für komplexe Operationen)
-- ============================================================

-- Procedure: Meme-View inkrementieren
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS increment_meme_view(
    IN p_meme_id INT UNSIGNED,
    IN p_ip_address VARCHAR(45),
    IN p_user_agent TEXT
)
BEGIN
    -- View-Counter erhöhen
    UPDATE memes SET views = views + 1 WHERE id = p_meme_id;

    -- View-Eintrag erstellen (für Analytics)
    INSERT INTO meme_views (meme_id, ip_address, user_agent)
    VALUES (p_meme_id, p_ip_address, p_user_agent);
END //
DELIMITER ;

-- ============================================================
-- Trigger für automatische Aktionen
-- ============================================================

-- Trigger: Tag-Usage-Counter aktualisieren
-- TODO: Bei Verwendung der normalisierten Tag-Tabelle aktivieren

-- ============================================================
-- Beispieldaten für Tests (optional)
-- ============================================================

-- Beispiel-Memes einfügen
-- TODO: Entfernen in Produktion
INSERT INTO memes (user_id, title, caption, file_path, file_name, file_size, file_type, tags) VALUES
(1, 'Erster Test-Meme', 'Das ist ein Test-Meme', '/app/public/memes/uploads/placeholder.jpg', 'placeholder.jpg', 0, 'image/jpeg', 'test,demo'),
(1, 'Zweiter Test-Meme', 'Noch ein Test', '/app/public/memes/uploads/placeholder2.jpg', 'placeholder2.jpg', 0, 'image/jpeg', 'test,lustig')
ON DUPLICATE KEY UPDATE id=id;

-- ============================================================
-- Ende des Schemas
-- ============================================================
