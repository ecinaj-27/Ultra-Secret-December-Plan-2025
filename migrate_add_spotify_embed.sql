-- Add Spotify embed support to media_items table
ALTER TABLE media_items 
ADD COLUMN spotify_embed TEXT AFTER external_link;

-- Update existing songs to have empty spotify_embed field
UPDATE media_items 
SET spotify_embed = '' 
WHERE type = 'song' AND spotify_embed IS NULL;
