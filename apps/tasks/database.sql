-- =============================================================================
-- TASK MANAGER DATABASE SCHEMA
-- =============================================================================
-- 
-- Creates the database and tables for the Task Manager application.
-- Includes sample data for immediate testing.
-- 
-- @author  Afzal Khan
-- @version 1.0.0
-- @since   January 2026
-- =============================================================================

-- Create database
CREATE DATABASE IF NOT EXISTS task_manager_db;
USE task_manager_db;

-- =============================================================================
-- CATEGORIES TABLE
-- Stores task categories for organization
-- =============================================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#3b82f6',  -- Hex color code
    icon VARCHAR(50) DEFAULT 'fa-folder', -- Font Awesome icon class
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================================================
-- TASKS TABLE
-- Main tasks table with priority, due dates, and status
-- =============================================================================
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- =============================================================================
-- SAMPLE DATA - Categories
-- =============================================================================
INSERT INTO categories (name, color, icon) VALUES
('Work', '#ef4444', 'fa-briefcase'),
('Personal', '#10b981', 'fa-user'),
('Study', '#8b5cf6', 'fa-book'),
('Health', '#f59e0b', 'fa-heart'),
('Shopping', '#ec4899', 'fa-shopping-cart');

-- =============================================================================
-- SAMPLE DATA - Tasks
-- =============================================================================
INSERT INTO tasks (title, description, category_id, priority, status, due_date) VALUES
('Complete project proposal', 'Write and submit the Q1 project proposal to management', 1, 'high', 'in_progress', DATE_ADD(CURDATE(), INTERVAL 2 DAY)),
('Team meeting preparation', 'Prepare slides and agenda for Monday team meeting', 1, 'medium', 'pending', DATE_ADD(CURDATE(), INTERVAL 3 DAY)),
('Code review', 'Review pull requests from junior developers', 1, 'medium', 'pending', DATE_ADD(CURDATE(), INTERVAL 1 DAY)),
('Buy groceries', 'Milk, eggs, bread, fruits, vegetables', 5, 'low', 'pending', CURDATE()),
('Gym workout', 'Leg day - squats, lunges, leg press', 4, 'medium', 'completed', CURDATE()),
('Read book chapter', 'Read chapter 5 of Clean Code', 3, 'low', 'pending', DATE_ADD(CURDATE(), INTERVAL 5 DAY)),
('Doctor appointment', 'Annual health checkup at 10 AM', 4, 'high', 'pending', DATE_ADD(CURDATE(), INTERVAL 7 DAY)),
('Learn React hooks', 'Complete the online tutorial on React hooks', 3, 'medium', 'in_progress', DATE_ADD(CURDATE(), INTERVAL 4 DAY)),
('Pay utility bills', 'Electricity, water, and internet bills', 2, 'high', 'pending', DATE_ADD(CURDATE(), INTERVAL 1 DAY)),
('Birthday gift for mom', 'Find and order a nice gift for mom birthday', 2, 'medium', 'pending', DATE_ADD(CURDATE(), INTERVAL 10 DAY));
