-- =============================================
-- Build Mode: Extend molecules table for custom molecules
-- =============================================

-- Add is_custom flag to distinguish user-built molecules from seed data
ALTER TABLE molecules
  MODIFY COLUMN category ENUM('essential','organic','drug','macro','custom') NOT NULL DEFAULT 'essential',
  ADD COLUMN is_custom TINYINT(1) NOT NULL DEFAULT 0 AFTER structure_data;
