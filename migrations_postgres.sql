-- Modify existing expenses table
ALTER TABLE expenses 
    ALTER COLUMN currency TYPE varchar(10),
    ALTER COLUMN currency SET DEFAULT 'INR';

ALTER TABLE expenses 
    ADD COLUMN group_id INTEGER NULL,
    ADD COLUMN ocr_status VARCHAR(50) DEFAULT 'none' CHECK (ocr_status IN ('none', 'pending', 'completed'));

-- Expense splits mapping
CREATE TABLE IF NOT EXISTS expense_splits (
    id SERIAL PRIMARY KEY,
    expense_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    share_amount DECIMAL(15,2) NOT NULL,
    share_percent DECIMAL(5,2) NULL,
    FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE
);
CREATE INDEX idx_expense_splits_user_id ON expense_splits(user_id);

-- Normalized balances table
CREATE TABLE IF NOT EXISTS balances (
    id SERIAL PRIMARY KEY,
    user_from INTEGER NOT NULL,
    user_to INTEGER NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_from, user_to)
);

-- Payments / Settlements
CREATE TABLE IF NOT EXISTS payments (
    id SERIAL PRIMARY KEY,
    from_user INTEGER NOT NULL,
    to_user INTEGER NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    group_id INTEGER NULL,
    note TEXT,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CSV Imports
CREATE TABLE IF NOT EXISTS imports (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    type VARCHAR(50) DEFAULT 'csv',
    raw_url VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'uploaded' CHECK (status IN ('uploaded', 'previewed', 'committed', 'failed')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
