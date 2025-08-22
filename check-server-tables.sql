-- Diagnostic script to check table structures on server
-- Run this first to understand why foreign key constraints are failing

-- Check if tables exist
SELECT 'Checking if tables exist:' as info;
SHOW TABLES LIKE 'branches';
SHOW TABLES LIKE 'businesses';
SHOW TABLES LIKE 'clients';

-- Check table engines
SELECT 'Checking table engines:' as info;
SELECT TABLE_NAME, ENGINE 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME IN ('branches', 'businesses', 'clients');

-- Check structure of branches table
SELECT 'Checking branches table structure:' as info;
DESCRIBE branches;

-- Check structure of businesses table  
SELECT 'Checking businesses table structure:' as info;
DESCRIBE businesses;

-- Check indexes on branches table
SELECT 'Checking indexes on branches table:' as info;
SHOW INDEX FROM branches;

-- Check indexes on businesses table
SELECT 'Checking indexes on businesses table:' as info;
SHOW INDEX FROM businesses;

-- Check foreign key constraints
SELECT 'Checking existing foreign key constraints:' as info;
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
AND REFERENCED_TABLE_NAME IS NOT NULL;
