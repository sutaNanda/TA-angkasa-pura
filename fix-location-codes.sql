-- ========================================
-- SCRIPT: Fix Location Codes for QR Scanner
-- ========================================

-- Step 1: Check if code column exists
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'db_asset_monitoring' 
  AND TABLE_NAME = 'locations' 
  AND COLUMN_NAME = 'code';

-- If above returns empty, run this to add column:
-- ALTER TABLE locations ADD COLUMN code VARCHAR(50) NULL UNIQUE AFTER id;
-- ALTER TABLE locations ADD INDEX idx_code (code);

-- Step 2: Check current location codes
SELECT id, name, code 
FROM locations 
ORDER BY id;

-- Step 3: Populate codes for locations that don't have them
-- This will assign LOC-001, LOC-002, etc.

SET @counter = 0;

UPDATE locations 
SET code = CONCAT('LOC-', LPAD((@counter := @counter + 1), 3, '0'))
WHERE code IS NULL
ORDER BY id;

-- Step 4: Verify all locations now have codes
SELECT id, name, code 
FROM locations 
ORDER BY id;

-- Step 5: Check for any NULL codes (should be 0)
SELECT COUNT(*) as locations_without_code
FROM locations 
WHERE code IS NULL;

-- ========================================
-- EXPECTED RESULT:
-- All locations should now have codes like:
-- LOC-001, LOC-002, LOC-003, etc.
-- ========================================
