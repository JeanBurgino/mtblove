-- Add uuid field to designs table for Amazon Merch design identification
-- The UUID comes from the Edit Url: https://merch.amazon.com/designs/{uuid}/edit

ALTER TABLE `designs`
ADD COLUMN `uuid` VARCHAR(36) NULL COMMENT 'Amazon Merch Design UUID from Edit URL' AFTER `slug`,
ADD UNIQUE INDEX `idx_uuid` (`uuid`);
