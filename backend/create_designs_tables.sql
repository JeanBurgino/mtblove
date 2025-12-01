-- ============================================================================
-- MTB Love - Design Shop Tables Migration
-- ============================================================================
-- This file creates the database structure for the design shop system
--
-- Tables created:
-- 1. designs        - Main design information
-- 2. markets        - Country/market configuration with affiliate tags
-- 3. product_types  - Product categories (T-Shirt, Hoodie, etc.)
-- 4. variants       - Links designs to markets and product types with ASINs
-- ============================================================================

-- Set character set for proper encoding
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ============================================================================
-- Table A: designs (Main Design Table)
-- Stores design information that is the same across all countries and products
-- ============================================================================

CREATE TABLE IF NOT EXISTS `designs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL COMMENT 'Design name, e.g. "Vintage Cat Dad"',
  `slug` VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL-friendly slug, e.g. "vintage-cat-dad"',
  `mockup_image_url` VARCHAR(500) NULL COMMENT 'Path to main preview image',
  `description` TEXT NULL COMMENT 'SEO text for the design',
  `tags` VARCHAR(500) NULL COMMENT 'Search keywords, comma-separated',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Soft delete flag: 1=active, 0=deleted',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_slug` (`slug`),
  INDEX `idx_active` (`is_active`),
  INDEX `idx_created` (`created_at` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Design information shared across all markets and products';

-- ============================================================================
-- Table B: markets (Country/Market Configuration)
-- Defines available markets with country-specific settings
-- Allows global changes (e.g. updating affiliate tags)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `markets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `country_code` VARCHAR(2) NOT NULL UNIQUE COMMENT 'ISO country code, e.g. "DE", "US", "UK"',
  `country_name` VARCHAR(100) NOT NULL COMMENT 'Full country name, e.g. "Germany"',
  `base_url` VARCHAR(255) NOT NULL COMMENT 'Amazon base URL, e.g. "https://www.amazon.de"',
  `affiliate_tag` VARCHAR(100) NOT NULL COMMENT 'Your affiliate/partner ID for this market',
  `currency_symbol` VARCHAR(10) NOT NULL COMMENT 'Currency symbol: €, $, £',
  `currency_code` VARCHAR(3) NOT NULL COMMENT 'ISO currency code: EUR, USD, GBP',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Market enabled/disabled',
  `display_order` INT NOT NULL DEFAULT 0 COMMENT 'Sort order for display',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_country_code` (`country_code`),
  INDEX `idx_active` (`is_active`),
  INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Market/country configuration with affiliate settings';

-- ============================================================================
-- Table C: product_types (Product Categories)
-- Defines available product types (T-Shirt, Hoodie, iPhone Case, etc.)
-- Makes it easy to add new product types without code changes
-- ============================================================================

CREATE TABLE IF NOT EXISTS `product_types` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT 'Product name, e.g. "Standard T-Shirt", "Hoodie"',
  `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT 'URL-friendly identifier',
  `description` TEXT NULL COMMENT 'Product description',
  `icon_class` VARCHAR(100) NULL COMMENT 'Optional CSS class for icon display',
  `display_order` INT NOT NULL DEFAULT 0 COMMENT 'Sort order: 1, 2, 3 (T-Shirts first, etc.)',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Product type enabled/disabled',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_slug` (`slug`),
  INDEX `idx_active` (`is_active`),
  INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Product types/categories for shop items';

-- ============================================================================
-- Table D: variants (The Magic Table - Links Everything Together)
-- Connects designs to markets and product types with Amazon ASINs
-- Only stores ASIN - full affiliate links are built dynamically
-- ============================================================================

CREATE TABLE IF NOT EXISTS `variants` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `design_id` INT UNSIGNED NOT NULL COMMENT 'Reference to designs table',
  `market_id` INT UNSIGNED NOT NULL COMMENT 'Reference to markets table',
  `product_type_id` INT UNSIGNED NOT NULL COMMENT 'Reference to product_types table',
  `asin` VARCHAR(20) NOT NULL COMMENT 'Amazon Product ID (ASIN), e.g. "B08XV..."',
  `price` DECIMAL(10,2) NULL COMMENT 'Optional: Product price for display',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Variant active flag (out of stock, deleted, etc.)',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- Foreign key constraints
  CONSTRAINT `fk_variants_design`
    FOREIGN KEY (`design_id`) REFERENCES `designs`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_variants_market`
    FOREIGN KEY (`market_id`) REFERENCES `markets`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_variants_product_type`
    FOREIGN KEY (`product_type_id`) REFERENCES `product_types`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,

  -- Indexes for performance
  INDEX `idx_design_id` (`design_id`),
  INDEX `idx_market_id` (`market_id`),
  INDEX `idx_product_type_id` (`product_type_id`),
  INDEX `idx_asin` (`asin`),
  INDEX `idx_active` (`is_active`),

  -- Unique constraint: one ASIN per design+market+product combination
  UNIQUE KEY `uk_design_market_product` (`design_id`, `market_id`, `product_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Variant combinations linking designs to markets and products with ASINs';

-- ============================================================================
-- Views for easier querying
-- ============================================================================

-- View: active_designs - Shows only active designs
CREATE OR REPLACE VIEW `active_designs` AS
SELECT * FROM `designs` WHERE `is_active` = 1;

-- View: active_markets - Shows only active markets
CREATE OR REPLACE VIEW `active_markets` AS
SELECT * FROM `markets` WHERE `is_active` = 1
ORDER BY `display_order`;

-- View: active_product_types - Shows only active product types
CREATE OR REPLACE VIEW `active_product_types` AS
SELECT * FROM `product_types` WHERE `is_active` = 1
ORDER BY `display_order`;

-- View: active_variants - Shows only active variants with full details
CREATE OR REPLACE VIEW `active_variants` AS
SELECT
  v.id,
  v.design_id,
  v.market_id,
  v.product_type_id,
  v.asin,
  v.price,
  v.is_active,
  v.created_at,
  v.updated_at,
  d.title AS design_title,
  d.slug AS design_slug,
  d.mockup_image_url,
  m.country_code,
  m.country_name,
  m.base_url,
  m.affiliate_tag,
  m.currency_symbol,
  m.currency_code,
  pt.name AS product_type_name,
  pt.slug AS product_type_slug,
  pt.icon_class,
  -- Build complete affiliate link
  CONCAT(m.base_url, '/dp/', v.asin, '?tag=', m.affiliate_tag) AS affiliate_link
FROM `variants` v
INNER JOIN `designs` d ON v.design_id = d.id
INNER JOIN `markets` m ON v.market_id = m.id
INNER JOIN `product_types` pt ON v.product_type_id = pt.id
WHERE v.is_active = 1
  AND d.is_active = 1
  AND m.is_active = 1
  AND pt.is_active = 1;

-- ============================================================================
-- Sample Data (Optional - for testing)
-- ============================================================================

-- Sample markets (German, US, UK)
INSERT INTO `markets` (`country_code`, `country_name`, `base_url`, `affiliate_tag`, `currency_symbol`, `currency_code`, `display_order`) VALUES
('DE', 'Germany', 'https://www.amazon.de', 'mtblove-21', '€', 'EUR', 1),
('US', 'United States', 'https://www.amazon.com', 'mtblove-20', '$', 'USD', 2),
('UK', 'United Kingdom', 'https://www.amazon.co.uk', 'mtblove-21', '£', 'GBP', 3)
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- Sample product types
INSERT INTO `product_types` (`name`, `slug`, `description`, `display_order`) VALUES
('Standard T-Shirt', 't-shirt', 'Classic cotton t-shirt', 1),
('Hoodie', 'hoodie', 'Comfortable pullover hoodie', 2),
('Tank Top', 'tank-top', 'Sleeveless athletic tank', 3),
('Long Sleeve', 'long-sleeve', 'Long sleeve t-shirt', 4),
('iPhone Case', 'iphone-case', 'Protective phone case', 5)
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- ============================================================================
-- Migration Complete
-- ============================================================================

SELECT 'Migration completed successfully!' AS status;
SELECT 'Tables created: designs, markets, product_types, variants' AS info;
SELECT 'Views created: active_designs, active_markets, active_product_types, active_variants' AS info;
