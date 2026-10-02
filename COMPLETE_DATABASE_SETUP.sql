-- =============================================================================
-- PORTFOLIO COMPLETE DATABASE SETUP
-- =============================================================================
-- This file contains ALL tables needed for the entire portfolio
-- Import this ONCE into InfinityFree phpMyAdmin
-- 
-- Database Name: if0_40964515_my_portfolio_db (your InfinityFree database)
-- 
-- Projects included:
--   1. Shop (E-commerce)
--   2. Blog CMS
--   3. Auth System
--   4. Task Manager
-- 
-- Other projects (Chat, Weather, Bookings, etc.) use localStorage only
-- 
-- @author  Afzal Khan
-- @since   January 2026
-- =============================================================================

-- NOTE: InfinityFree database already exists, so we DON'T create it
-- USE if0_40964515_my_portfolio_db;  -- Uncomment if needed

-- =============================================================================
-- SHOP / E-COMMERCE TABLES
-- =============================================================================

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    stock INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50),
    customer_address TEXT,
    customer_city VARCHAR(100),
    order_total DECIMAL(10,2),
    cart_items TEXT,
    status ENUM('pending', 'processing', 'shipped', 'delivered') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample Products
INSERT INTO products (name, description, price, image_url, stock) VALUES 
('CyberDeck 2077', 'A split ergonomic mechanical keyboard with OLED displays and rotary encoders. Perfect for the cyberpunk aesthetic.', 299.99, 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=600&h=600&fit=crop', 5),
('Zenith 65% Brass', 'Heavy brass weight, gasket mounted 65% keyboard kit. Anodized aluminum case in Deep Navy.', 189.50, 'https://images.unsplash.com/photo-1626218174397-91aee348c4cf?w=600&h=600&fit=crop', 10),
('Nebula Resin Keycaps', 'Hand-cast artisan keycap set with galaxy swirls and gold flakes. Cherry profile, 120 keys.', 85.00, 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=600&h=600&fit=crop', 20),
('Stealth Ops Deskmat', 'Water-resistant, high-density cloth deskmat. 900x400mm. Stealth black geometric pattern.', 24.99, 'https://images.unsplash.com/photo-1615663245857-acda847f842b?w=600&h=600&fit=crop', 50);

-- =============================================================================
-- BLOG CMS TABLES
-- =============================================================================

CREATE TABLE IF NOT EXISTS blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    content TEXT,
    excerpt TEXT,
    featured_image VARCHAR(255),
    category_id INT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(100),
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
);

-- Sample Blog Categories
INSERT INTO blog_categories (name, slug, description) VALUES
('Technology', 'technology', 'Latest tech news and tutorials'),
('Programming', 'programming', 'Coding tips and best practices'),
('Web Development', 'web-development', 'Frontend and backend development'),
('Design', 'design', 'UI/UX and graphic design'),
('Tutorials', 'tutorials', 'Step-by-step guides');

-- Sample Blog Posts
INSERT INTO blog_posts (title, slug, content, excerpt, featured_image, category_id, status, views) VALUES
('Getting Started with PHP 8', 'getting-started-php-8', 
'<p>PHP 8 brings many exciting features that make development faster and more enjoyable. In this post, we will explore the key features you should know.</p><h2>Named Arguments</h2><p>Named arguments allow you to pass values to a function by specifying the parameter name. This makes your code more readable.</p><h2>Attributes</h2><p>Attributes provide a way to add metadata to classes, methods, and properties without using docblocks.</p><h2>Union Types</h2><p>You can now declare that a parameter or return type can be one of several types.</p><p>These are just a few of the many improvements in PHP 8. Stay tuned for more tutorials!</p>',
'Discover the exciting new features in PHP 8 that make development faster and more enjoyable.',
'https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=800', 2, 'published', 245),

('Building REST APIs with Node.js', 'building-rest-apis-nodejs',
'<p>REST APIs are the backbone of modern web applications. In this comprehensive guide, we will learn how to build robust APIs using Node.js and Express.</p><h2>Setting Up Express</h2><p>First, we need to set up our project and install dependencies...</p><h2>Creating Routes</h2><p>Express makes it easy to define routes for different HTTP methods...</p><h2>Error Handling</h2><p>Proper error handling is crucial for production APIs...</p>',
'Learn how to create powerful REST APIs using Node.js and Express with best practices.',
'https://images.unsplash.com/photo-1627398242454-45a1465c2479?w=800', 3, 'published', 189),

('UI Design Principles for Developers', 'ui-design-principles-developers',
'<p>As a developer, understanding basic design principles can significantly improve your work. Here are essential concepts every developer should know.</p><h2>Visual Hierarchy</h2><p>Not all elements are equal. Use size, color, and spacing to guide the users eye.</p><h2>Consistency</h2><p>Keep your designs consistent throughout the application...</p><h2>White Space</h2><p>Do not be afraid of empty space. It helps content breathe and improves readability.</p>',
'Essential UI design principles that every developer should know to create better interfaces.',
'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800', 4, 'published', 156),

('JavaScript ES6+ Features You Should Use', 'javascript-es6-features',
'<p>Modern JavaScript has evolved significantly. Here are the ES6+ features that will make your code cleaner and more efficient.</p><h2>Arrow Functions</h2><p>Arrow functions provide a concise syntax and lexical this binding...</p><h2>Destructuring</h2><p>Extract values from arrays and objects with elegant syntax...</p><h2>Template Literals</h2><p>Create strings with embedded expressions easily...</p>',
'Master the essential ES6+ JavaScript features that make coding more efficient and enjoyable.',
'https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?w=800', 2, 'published', 312),

('Complete Guide to CSS Grid', 'complete-guide-css-grid',
'<p>CSS Grid is a powerful layout system that revolutionizes how we design web pages. This guide covers everything from basics to advanced techniques.</p><h2>Grid Container</h2><p>Create a grid container using display: grid...</p><h2>Defining Tracks</h2><p>Use grid-template-columns and grid-template-rows...</p>',
'Everything you need to know about CSS Grid layout system for modern web design.',
'https://images.unsplash.com/photo-1507721999472-8ed4421c4af2?w=800', 3, 'draft', 0);

-- Sample Blog Comments
INSERT INTO blog_comments (post_id, author_name, author_email, content, status) VALUES
(1, 'Marcus Chen', 'marcus@example.com', 'Great introduction to PHP 8! The named arguments feature is a game changer.', 'approved'),
(1, 'Sarah Williams', 'sarah@example.com', 'Finally a clear explanation of attributes. Thanks!', 'approved'),
(2, 'Mike Backend', 'mike@example.com', 'Very helpful for understanding REST API design patterns.', 'approved'),
(3, 'Emma Designer', 'emma@example.com', 'As a developer trying to improve my design skills, this is exactly what I needed.', 'approved'),
(4, 'Alex Rivera', 'alex@example.com', 'Arrow functions and destructuring are my favorite features!', 'approved');

-- =============================================================================
-- AUTHENTICATION SYSTEM TABLES
-- =============================================================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Demo Users (password: demo123)
INSERT INTO users (username, email, password, full_name) VALUES
('demo', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo User'),
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- =============================================================================
-- TASK MANAGER TABLES
-- =============================================================================

CREATE TABLE IF NOT EXISTS task_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#3b82f6',
    icon VARCHAR(50) DEFAULT 'fa-folder',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
    FOREIGN KEY (category_id) REFERENCES task_categories(id) ON DELETE SET NULL
);

-- Sample Task Categories
INSERT INTO task_categories (name, color, icon) VALUES
('Work', '#ef4444', 'fa-briefcase'),
('Personal', '#10b981', 'fa-user'),
('Study', '#8b5cf6', 'fa-book'),
('Health', '#f59e0b', 'fa-heart'),
('Shopping', '#ec4899', 'fa-shopping-cart');

-- Sample Tasks
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

-- =============================================================================
-- SETUP COMPLETE!
-- =============================================================================
-- All tables have been created and sample data inserted.
-- 
-- PROJECTS NOT USING DATABASE:
-- - Weather (API only)
-- - Chat (localStorage)
-- - Bookings (localStorage)
-- - Expenses (localStorage)
-- - Quiz (localStorage)
-- - Shorturl (localStorage)
-- - Social (localStorage)
-- - Manage (demo data in JavaScript)
-- - Checkout (demo)
-- - OTP (demo)
-- - Assistant (AI chatbot, no DB)
-- 
-- LOGIN CREDENTIALS:
-- Username: demo
-- Password: demo123
-- =============================================================================
