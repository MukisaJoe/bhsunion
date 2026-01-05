-- Clear all data from database except admin account
-- This will delete all contributions, chats, announcements, etc.
-- Admin account (James Smith) will be preserved

-- Step 1: Clear all contributions
DELETE FROM contributions;
ALTER SEQUENCE contributions_id_seq RESTART WITH 1;

-- Step 2: Clear all chat messages
DELETE FROM chat_messages;
ALTER SEQUENCE chat_messages_id_seq RESTART WITH 1;

-- Step 3: Clear all chat reactions
DELETE FROM chat_reactions;
ALTER SEQUENCE chat_reactions_id_seq RESTART WITH 1;

-- Step 4: Clear all announcements
DELETE FROM announcements;
ALTER SEQUENCE announcements_id_seq RESTART WITH 1;

-- Step 5: Clear all withdrawals
DELETE FROM withdrawals;
ALTER SEQUENCE withdrawals_id_seq RESTART WITH 1;

-- Step 6: Clear all treasury adjustments
DELETE FROM treasury_adjustments;
ALTER SEQUENCE treasury_adjustments_id_seq RESTART WITH 1;

-- Step 7: Clear all messages
DELETE FROM messages;
ALTER SEQUENCE messages_id_seq RESTART WITH 1;

-- Step 8: Clear all audit logs
DELETE FROM audit_logs;
ALTER SEQUENCE audit_logs_id_seq RESTART WITH 1;

-- Step 9: Clear monthly settings
DELETE FROM monthly_settings;
ALTER SEQUENCE monthly_settings_id_seq RESTART WITH 1;

-- Step 10: Clear app settings (optional - this will reset current period, about page, etc.)
DELETE FROM app_settings;
ALTER SEQUENCE app_settings_id_seq RESTART WITH 1;

-- Step 11: Clear client logs
DELETE FROM client_logs;
ALTER SEQUENCE client_logs_id_seq RESTART WITH 1;

-- Step 12: Clear session tokens (logout all users)
DELETE FROM session_tokens;
ALTER SEQUENCE session_tokens_id_seq RESTART WITH 1;

-- Step 13: Clear rate limits
DELETE FROM rate_limits;
ALTER SEQUENCE rate_limits_id_seq RESTART WITH 1;

-- Step 14: Delete all users except admin (ID = 1, James Smith)
-- First, let's check if there are other users
DELETE FROM users WHERE id != 1;
ALTER SEQUENCE users_id_seq RESTART WITH 2;

-- Verification queries (optional - run these to verify)
-- SELECT COUNT(*) as admin_count FROM users WHERE id = 1;
-- SELECT COUNT(*) as other_users FROM users WHERE id != 1;
-- SELECT COUNT(*) as contributions_count FROM contributions;
-- SELECT COUNT(*) as chat_messages_count FROM chat_messages;
-- SELECT COUNT(*) as announcements_count FROM announcements;

