-- Migration: Add triggers for auto-updating updated_at columns
-- This migration adds triggers to automatically update updated_at timestamps

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Trigger for users table
DROP TRIGGER IF EXISTS update_users_updated_at ON users;
CREATE TRIGGER update_users_updated_at 
    BEFORE UPDATE ON users
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

-- Trigger for app_settings table
DROP TRIGGER IF EXISTS update_app_settings_updated_at ON app_settings;
CREATE TRIGGER update_app_settings_updated_at 
    BEFORE UPDATE ON app_settings
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

-- Trigger for rate_limits table
DROP TRIGGER IF EXISTS update_rate_limits_updated_at ON rate_limits;
CREATE TRIGGER update_rate_limits_updated_at 
    BEFORE UPDATE ON rate_limits
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

