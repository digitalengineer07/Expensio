-- Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    profile_pic VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    user_id INT, -- NULL for global categories
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    type VARCHAR(50) DEFAULT 'expense' CHECK (type IN ('expense', 'income')),
    is_global BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Projects Table
CREATE TABLE IF NOT EXISTS projects (
    id SERIAL PRIMARY KEY,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    budget DECIMAL(15, 2),
    status VARCHAR(50) DEFAULT 'active' CHECK (status IN ('active', 'completed', 'on_hold')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Expenses Table
CREATE TABLE IF NOT EXISTS expenses (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT,
    project_id INT,
    amount DECIMAL(15, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'INR',
    description TEXT,
    expense_date DATE NOT NULL,
    receipt_path VARCHAR(255),
    is_recurring BOOLEAN DEFAULT FALSE,
    recurring_rule_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    group_id INTEGER NULL,
    ocr_status VARCHAR(50) DEFAULT 'none' CHECK (ocr_status IN ('none', 'pending', 'completed')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
);

-- Recurring Rules Table
CREATE TABLE IF NOT EXISTS recurring_rules (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    frequency VARCHAR(50) NOT NULL CHECK (frequency IN ('daily', 'weekly', 'monthly', 'yearly')),
    interval_value INT DEFAULT 1,
    start_date DATE NOT NULL,
    end_date DATE,
    next_occurrence DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id SERIAL PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Blog Posts
CREATE TABLE IF NOT EXISTS blog_posts (
    id SERIAL PRIMARY KEY,
    author_id INT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'draft' CHECK (status IN ('draft', 'published')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert Default Roles
INSERT INTO roles (name, description) VALUES 
('Admin', 'System administrator with full access'),
('Student', 'Registered resident role for students'),
('Engineer', 'Registered resident role for civil engineers')
ON CONFLICT (name) DO NOTHING;

-- Insert Global Categories
INSERT INTO categories (name, icon, type, is_global) VALUES 
('Food & Dining', 'utensils', 'expense', TRUE),
('Transportation', 'car', 'expense', TRUE),
('Utilities', 'lightbulb', 'expense', TRUE),
('Education', 'graduation-cap', 'expense', TRUE),
('Materials', 'hammer', 'expense', TRUE),
('Labor', 'users', 'expense', TRUE),
('Rent', 'home', 'expense', TRUE),
('Salary', 'briefcase', 'income', TRUE),
('Freelance', 'laptop', 'income', TRUE),
('Other Income', 'coins', 'income', TRUE),
('Miscellaneous', 'ellipsis-h', 'expense', TRUE);

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
