-- Migration: Remove path prefix from variants.mockup_image_url
-- Store only filename (not full path) in variants.mockup_image_url
-- Frontend will prepend MOCKUP_PATH when displaying images

-- Update variants table: remove '/uploads/mockups/' prefix from mockup_image_url
UPDATE variants
SET mockup_image_url = REPLACE(mockup_image_url, '/uploads/mockups/', '')
WHERE mockup_image_url LIKE '/uploads/mockups/%';

-- Verify the changes
SELECT id, product_type_id, mockup_image_url
FROM variants
WHERE mockup_image_url IS NOT NULL
LIMIT 10;
