-- Modify existing expenses table
ALTER TABLE expenses
    MODIFY COLUMN currency varchar(10) DEFAULT 'INR',
    ADD COLUMN group_id int(11) NULL,
    ADD COLUMN ocr_status ENUM('none', 'pending', 'completed') DEFAULT 'none';

-- Expense splits mapping
CREATE TABLE IF NOT EXISTS expense_splits (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    expense_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    share_amount DECIMAL(15,2) NOT NULL,
    share_percent DECIMAL(5,2) NULL,
    FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Normalized balances table
CREATE TABLE IF NOT EXISTS balances (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_from INT(11) NOT NULL,
    user_to INT(11) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_pair (user_from, user_to)
);

-- Payments / Settlements
CREATE TABLE IF NOT EXISTS payments (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    from_user INT(11) NOT NULL,
    to_user INT(11) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    group_id INT(11) NULL,
    note TEXT,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- CSV Imports
CREATE TABLE IF NOT EXISTS imports (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    type VARCHAR(50) DEFAULT 'csv',
    raw_url VARCHAR(255) NOT NULL,
    status ENUM('uploaded', 'previewed', 'committed', 'failed') DEFAULT 'uploaded',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
