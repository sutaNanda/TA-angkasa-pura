-- Check if code column exists in locations table
SHOW COLUMNS FROM locations LIKE 'code';

-- If column exists, check location codes
SELECT id, name, code FROM locations;

-- Count locations with codes
SELECT 
    COUNT(*) as total_locations,
    COUNT(code) as locations_with_code,
    COUNT(*) - COUNT(code) as locations_without_code
FROM locations;
