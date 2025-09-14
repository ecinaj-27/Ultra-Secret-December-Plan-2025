-- Migration script to add caption columns to timeline_events and locations tables
-- Run this if you haven't updated your database with the new schema

USE secret_plan_db;

-- Add caption column to timeline_events table if it doesn't exist
ALTER TABLE timeline_events 
ADD COLUMN IF NOT EXISTS caption TEXT AFTER image_path;

-- Add caption column to locations table if it doesn't exist  
ALTER TABLE locations 
ADD COLUMN IF NOT EXISTS caption TEXT AFTER description;

-- Update existing records with default captions if they don't have any
UPDATE timeline_events 
SET caption = CONCAT('Memory from ', event_date) 
WHERE caption IS NULL OR caption = '';

UPDATE locations 
SET caption = CONCAT('Visit to ', name) 
WHERE caption IS NULL OR caption = '';

-- Verify the changes
SELECT 'Timeline events with captions:' as info;
SELECT id, title, caption FROM timeline_events LIMIT 5;

SELECT 'Locations with captions:' as info;
SELECT id, name, caption FROM locations LIMIT 5;
