-- PostgreSQL schema for Bhs Union API

CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(190) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'member' CHECK (role IN ('admin', 'member')),
  status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('active', 'pending', 'disabled')),
  phone VARCHAR(40) DEFAULT NULL,
  provider VARCHAR(60) DEFAULT NULL,
  mobile_money_number VARCHAR(40) DEFAULT NULL,
  other_number VARCHAR(40) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS session_tokens (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contributions (
  id SERIAL PRIMARY KEY,
  member_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  month VARCHAR(20) NOT NULL,
  year INT NOT NULL,
  amount NUMERIC(12, 2) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'rejected')),
  submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  confirmed_at TIMESTAMP DEFAULT NULL,
  confirmed_by INT REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_contrib_member_period ON contributions (member_id, year, month);
CREATE INDEX IF NOT EXISTS idx_contrib_status ON contributions (status);

CREATE TABLE IF NOT EXISTS withdrawals (
  id SERIAL PRIMARY KEY,
  admin_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  amount NUMERIC(12, 2) NOT NULL,
  reason VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_withdrawals_created ON withdrawals (created_at);

CREATE TABLE IF NOT EXISTS announcements (
  id SERIAL PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  content TEXT NOT NULL,
  created_by INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  published BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_announcements_created ON announcements (created_at);

CREATE TABLE IF NOT EXISTS chat_messages (
  id SERIAL PRIMARY KEY,
  sender_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  message TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  edited_at TIMESTAMP DEFAULT NULL,
  deleted_at TIMESTAMP DEFAULT NULL,
  deleted_by INT REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_chat_created ON chat_messages (created_at);

CREATE TABLE IF NOT EXISTS chat_reactions (
  id SERIAL PRIMARY KEY,
  message_id INT NOT NULL REFERENCES chat_messages(id) ON DELETE CASCADE,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  emoji VARCHAR(12) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (message_id, user_id, emoji)
);
CREATE INDEX IF NOT EXISTS idx_reactions_message ON chat_reactions (message_id);

CREATE TABLE IF NOT EXISTS audit_logs (
  id SERIAL PRIMARY KEY,
  actor_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  action VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_logs (created_at);

CREATE TABLE IF NOT EXISTS messages (
  id SERIAL PRIMARY KEY,
  sender_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  subject VARCHAR(190) NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_messages_created ON messages (created_at);

CREATE TABLE IF NOT EXISTS monthly_settings (
  id SERIAL PRIMARY KEY,
  month VARCHAR(20) NOT NULL,
  year INT NOT NULL,
  amount NUMERIC(12, 2) NOT NULL,
  set_by INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (month, year)
);

CREATE TABLE IF NOT EXISTS app_settings (
  id SERIAL PRIMARY KEY,
  setting_key VARCHAR(120) NOT NULL UNIQUE,
  setting_value TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rate_limits (
  id SERIAL PRIMARY KEY,
  rate_key VARCHAR(190) NOT NULL UNIQUE,
  window_start TIMESTAMP NOT NULL,
  request_count INT NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS client_logs (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES users(id) ON DELETE SET NULL,
  level VARCHAR(20) NOT NULL,
  message TEXT NOT NULL,
  stack TEXT,
  platform VARCHAR(40),
  context VARCHAR(120),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS treasury_adjustments (
  id SERIAL PRIMARY KEY,
  admin_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  adjustment_type VARCHAR(20) NOT NULL CHECK (adjustment_type IN ('add', 'withdraw', 'initial', 'correction')),
  amount NUMERIC(12, 2) NOT NULL,
  reason VARCHAR(255) NOT NULL,
  previous_balance NUMERIC(12, 2) NOT NULL,
  new_balance NUMERIC(12, 2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_treasury_adjustments_created ON treasury_adjustments(created_at);
CREATE INDEX IF NOT EXISTS idx_treasury_adjustments_type ON treasury_adjustments(adjustment_type);
CREATE INDEX IF NOT EXISTS idx_treasury_adjustments_admin ON treasury_adjustments(admin_id);

-- Function to get current treasury balance
CREATE OR REPLACE FUNCTION get_treasury_balance()
RETURNS NUMERIC AS $$
DECLARE
  total_contributions NUMERIC := 0;
  total_withdrawals NUMERIC := 0;
  total_adjustments NUMERIC := 0;
  final_balance NUMERIC := 0;
BEGIN
  -- Sum all confirmed contributions
  SELECT COALESCE(SUM(amount), 0) INTO total_contributions
  FROM contributions
  WHERE status = 'confirmed';

  -- Sum all withdrawals
  SELECT COALESCE(SUM(amount), 0) INTO total_withdrawals
  FROM withdrawals;

  -- Sum all treasury adjustments
  SELECT COALESCE(SUM(
    CASE 
      WHEN adjustment_type IN ('add', 'initial', 'correction') THEN amount
      WHEN adjustment_type = 'withdraw' THEN -amount
      ELSE 0
    END
  ), 0) INTO total_adjustments
  FROM treasury_adjustments;

  -- Calculate final balance
  final_balance := total_contributions + total_adjustments - total_withdrawals;
  
  RETURN final_balance;
END;
$$ LANGUAGE plpgsql;
