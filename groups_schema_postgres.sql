-- PostgreSQL Schema for Expensio Groups Feature

-- 1. Updates required on the existing 'users' table
ALTER TABLE users ADD COLUMN IF NOT EXISTS subscription_status VARCHAR(50) DEFAULT 'free';
ALTER TABLE users ADD COLUMN IF NOT EXISTS trial_ends_at TIMESTAMP NULL DEFAULT NULL;

-- Set existing users to premium so you aren't blocked during testing
UPDATE users SET subscription_status = 'premium';

-- 2. Groups Core Table
CREATE TABLE IF NOT EXISTS groups (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_by INT REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Group Members (Junction Table)
CREATE TABLE IF NOT EXISTS group_members (
    group_id INT REFERENCES groups(id) ON DELETE CASCADE,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(group_id, user_id)
);

-- 4. Group Expenses Table
CREATE TABLE IF NOT EXISTS group_expenses (
    id SERIAL PRIMARY KEY,
    group_id INT REFERENCES groups(id) ON DELETE CASCADE,
    paid_by INT REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT,
    category_id INT NULL REFERENCES categories(id) ON DELETE SET NULL,
    split_type VARCHAR(50) DEFAULT 'equal',
    expense_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Individual Expense Splits Breakdown
CREATE TABLE IF NOT EXISTS group_expense_splits (
    id SERIAL PRIMARY KEY,
    group_expense_id INT REFERENCES group_expenses(id) ON DELETE CASCADE,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    amount_owed DECIMAL(15, 2) NOT NULL,
    is_settled BOOLEAN DEFAULT FALSE
);

-- 6. Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50), 
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for better PostgreSQL query performance
CREATE INDEX IF NOT EXISTS idx_group_members_user ON group_members(user_id);
CREATE INDEX IF NOT EXISTS idx_group_expenses_group ON group_expenses(group_id);
CREATE INDEX IF NOT EXISTS idx_group_expense_splits_expense ON group_expense_splits(group_expense_id);
