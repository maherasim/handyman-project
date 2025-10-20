-- Fix NULL values in total_views columns
-- Run this SQL in your database

-- Update services table
UPDATE services 
SET total_views = 0 
WHERE total_views IS NULL;

-- Update post_job_requests table
UPDATE post_job_requests 
SET total_views = 0 
WHERE total_views IS NULL;

-- Verify the changes
SELECT COUNT(*) as null_count_services 
FROM services 
WHERE total_views IS NULL;

SELECT COUNT(*) as null_count_jobs 
FROM post_job_requests 
WHERE total_views IS NULL;

-- Should both return 0
