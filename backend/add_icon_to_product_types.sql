-- ============================================================================
-- Migration: Add icon column to product_types table
-- ============================================================================
-- This migration adds the icon column to the existing product_types table
-- and updates the existing records with icon SVG filenames
-- ============================================================================

-- Add icon column after description
ALTER TABLE `product_types`
ADD COLUMN `icon` VARCHAR(100) NULL COMMENT 'SVG-Dateiname, z.B. "product-tshirt.svg"'
AFTER `description`;

-- Update existing records with icon SVG filenames
UPDATE `product_types` SET `icon` = 'product-tshirt.svg' WHERE `slug` = 't-shirt';
UPDATE `product_types` SET `icon` = 'product-hoodie.svg' WHERE `slug` = 'hoodie';
UPDATE `product_types` SET `icon` = 'product-tank-top.svg' WHERE `slug` = 'tank-top';
UPDATE `product_types` SET `icon` = 'product-long-sleeve.svg' WHERE `slug` = 'long-sleeve';
UPDATE `product_types` SET `icon` = 'product-iphone-case.svg' WHERE `slug` = 'iphone-case';

-- Recreate the active_variants view to include icon
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
    pt.icon AS product_type_icon,
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
SELECT 'Added icon column to product_types table' AS info;
SELECT 'Updated existing records with icon SVG filenames' AS info;
SELECT 'Recreated active_variants view' AS info;
