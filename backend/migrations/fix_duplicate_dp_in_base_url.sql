-- Fix duplicate /dp/ in base_url if it exists
-- This migration removes trailing /dp/ or /dp from base_url values
-- to prevent duplicate /dp/ in Amazon URLs

UPDATE markets
SET base_url = TRIM(TRAILING '/' FROM REPLACE(base_url, '/dp/', '/'))
WHERE base_url LIKE '%/dp/%' OR base_url LIKE '%/dp';

-- Verify the values are correct
-- Expected values:
-- DE: https://www.amazon.de
-- US: https://www.amazon.com
-- UK: https://www.amazon.co.uk

SELECT country_code, base_url
FROM markets
ORDER BY display_order;
