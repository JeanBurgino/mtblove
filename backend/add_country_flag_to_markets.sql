-- ============================================================================
-- Migration: Add country_flag column to markets table
-- ============================================================================
-- This migration adds the country_flag column to the existing markets table
-- and updates the existing records with flag SVG filenames
-- ============================================================================

-- Add country_flag column after country_name
ALTER TABLE `markets`
ADD COLUMN `country_flag` VARCHAR(100) NOT NULL COMMENT 'SVG-Dateiname, z.B. "flag-de.svg"'
AFTER `country_name`;

-- Update existing records with flag SVG filenames
UPDATE `markets` SET `country_flag` = 'flag-de.svg' WHERE `country_code` = 'DE';
UPDATE `markets` SET `country_flag` = 'flag-us.svg' WHERE `country_code` = 'US';
UPDATE `markets` SET `country_flag` = 'flag-uk.svg' WHERE `country_code` = 'UK';

-- Recreate the active_variants view to include country_flag
DROP VIEW IF EXISTS `active_variants`;

CREATE VIEW `active_variants` AS
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
    m.country_flag,
    m.base_url,
    m.affiliate_tag,
    m.currency_symbol,
    m.currency_code,
    pt.name AS product_type_name,
    pt.slug AS product_type_slug,
    pt.icon_class,
    -- Vollständigen Affiliate-Link generieren
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
-- Migration Complete
-- ============================================================================

SELECT 'Migration completed successfully!' AS status;
SELECT 'Added country_flag column to markets table' AS info;
SELECT 'Updated existing records with flag emojis' AS info;
SELECT 'Recreated active_variants view' AS info;
