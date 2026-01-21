-- =============================================================================
-- BLOG CMS DATABASE SCHEMA
-- =============================================================================
-- @author  Afzal Khan
-- @since   January 2026
-- =============================================================================

CREATE DATABASE IF NOT EXISTS blog_cms_db;
USE blog_cms_db;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Posts Table
CREATE TABLE IF NOT EXISTS posts (
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
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(100),
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Sample Categories
INSERT INTO categories (name, slug, description) VALUES
('Technology', 'technology', 'Latest tech news and tutorials'),
('Programming', 'programming', 'Coding tips and best practices'),
('Web Development', 'web-development', 'Frontend and backend development'),
('Design', 'design', 'UI/UX and graphic design'),
('Tutorials', 'tutorials', 'Step-by-step guides');

-- Sample Posts
INSERT INTO posts (title, slug, content, excerpt, featured_image, category_id, status, views) VALUES
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

-- Sample Comments
INSERT INTO comments (post_id, author_name, author_email, content, status) VALUES
(1, 'John Developer', 'john@example.com', 'Great introduction to PHP 8! The named arguments feature is a game changer.', 'approved'),
(1, 'Sarah Code', 'sarah@example.com', 'Finally a clear explanation of attributes. Thanks!', 'approved'),
(2, 'Mike Backend', 'mike@example.com', 'Very helpful for understanding REST API design patterns.', 'approved'),
(3, 'Emma Designer', 'emma@example.com', 'As a developer trying to improve my design skills, this is exactly what I needed.', 'approved'),
(4, 'Alex JS', 'alex@example.com', 'Arrow functions and destructuring are my favorite features!', 'approved');
