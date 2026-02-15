-- Fix payment_method enum in business_balance_histories table
-- Add 'insurance' and 'credit_arrangement' to the enum

ALTER TABLE business_balance_histories 
MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card', 'insurance', 'credit_arrangement') 
NULL DEFAULT 'mobile_money';
