-- Online Payment Management System Database Schema

CREATE DATABASE IF NOT EXISTS money_management;
USE money_management;

-- Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
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
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT, -- NULL for global categories
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    type ENUM('expense', 'income') DEFAULT 'expense',
    is_global BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Projects Table (mainly for Civil Engineers)
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    budget DECIMAL(15, 2),
    status ENUM('active', 'completed', 'on_hold') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Expenses Table
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
);

-- Recurring Rules Table
CREATE TABLE IF NOT EXISTS recurring_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    frequency ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL,
    interval_value INT DEFAULT 1,
    start_date DATE NOT NULL,
    end_date DATE,
    next_occurrence DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Blog Posts
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert Default Roles
INSERT IGNORE INTO roles (name, description) VALUES 
('Admin', 'System administrator with full access'),
('Student', 'Registered resident role for students'),
('Engineer', 'Registered resident role for civil engineers');

-- Insert Global Categories
INSERT IGNORE INTO categories (name, icon, type, is_global) VALUES 
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

