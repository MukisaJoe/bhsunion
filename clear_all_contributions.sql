-- Clear all contributions from the database
-- This will delete ALL contributions (pending, confirmed, and rejected)

-- Delete all contributions
DELETE FROM contributions;

-- Reset the sequence (optional, but good practice for a fresh start)
ALTER SEQUENCE contributions_id_seq RESTART WITH 1;

-- Verify deletion
SELECT COUNT(*) as remaining_contributions FROM contributions;

