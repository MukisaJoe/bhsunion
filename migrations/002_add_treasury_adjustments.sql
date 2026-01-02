-- Migration: Add treasury_adjustments table
-- This table tracks all treasury balance adjustments (additions, withdrawals, etc.)

CREATE TABLE IF NOT EXISTS treasury_adjustments (
  id SERIAL PRIMARY KEY,
  admin_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  adjustment_type VARCHAR(20) NOT NULL CHECK (adjustment_type IN ('add', 'withdraw', 'initial', 'correction')),
  amount NUMERIC(12, 2) NOT NULL,
  reason VARCHAR(255) NOT NULL,
  previous_balance NUMERIC(12, 2) NOT NULL,
  new_balance NUMERIC(12, 2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_treasury_adjustments_created ON treasury_adjustments(created_at);
CREATE INDEX IF NOT EXISTS idx_treasury_adjustments_type ON treasury_adjustments(adjustment_type);
CREATE INDEX IF NOT EXISTS idx_treasury_adjustments_admin ON treasury_adjustments(admin_id);

-- Add a function to get current treasury balance
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

