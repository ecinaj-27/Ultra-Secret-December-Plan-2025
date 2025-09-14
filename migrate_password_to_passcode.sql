-- Migration script to change password field to passcode
-- Run this script if your database still has the old 'password' field

-- First, check if the password field exists
-- If it does, rename it to passcode
ALTER TABLE users CHANGE COLUMN password passcode VARCHAR(255) NOT NULL;

-- Verify the change
-- You can run this to check: DESCRIBE users;
