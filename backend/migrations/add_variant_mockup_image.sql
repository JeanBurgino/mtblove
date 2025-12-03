-- Migration: Add mockup_image_url to variants table
-- Date: 2025-12-03
-- Description: Adds a mockup_image_url field to the variants table to allow
--              each variant to have its own product mockup image
--
-- Note: This field stores only the filename (e.g., "hoodie_mockup.jpg")
--       The path (/uploads/mockups/) is configured in config.php

-- Add mockup_image_url column to variants table
ALTER TABLE `variants`
ADD COLUMN `mockup_image_url` VARCHAR(500) NULL
COMMENT 'Dateiname des Produkt-Mockup-Bildes (ohne Pfad, z.B. "hoodie_mockup.jpg"). Pfad wird aus Config gelesen: /uploads/mockups/'
AFTER `price`;

-- Update the active_variants view to include the variant's mockup_image_url
CREATE OR REPLACE VIEW `active_variants` AS
SELECT
    v.id,
    v.design_id,
    v.market_id,
    v.product_type_id,
    v.asin,
    v.price,
    v.mockup_image_url AS variant_mockup_image_url,
    v.is_active,
    v.created_at,
    v.updated_at,
    d.title AS design_title,
    d.slug AS design_slug,
    d.mockup_image_url AS design_mockup_image_url,
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
