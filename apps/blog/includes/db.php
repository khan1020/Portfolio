<?php
// Smart database connection - works both locally and on InfinityFree
$isLocal = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']) || 
           strpos($_SERVER['SERVER_NAME'], '.local') !== false;

if ($isLocal) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "blog_cms_db";
    
    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
    
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->select_db($dbname);
    
    // Auto-create tables
    $conn->query("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $conn->query("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255),
        content TEXT,
        excerpt TEXT,
        featured_image VARCHAR(255),
        category_id INT,
        status ENUM('draft', 'published') DEFAULT 'draft',
        views INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    $conn->query("CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        author_name VARCHAR(100) NOT NULL,
        author_email VARCHAR(100),
        content TEXT NOT NULL,
        status ENUM('pending', 'approved', 'spam') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add sample data if empty
    if ($conn->query("SELECT COUNT(*) as c FROM blog_categories")->fetch_assoc()['c'] == 0) {
        $conn->query("INSERT INTO blog_categories (name, slug, description) VALUES 
            ('Technology', 'technology', 'Tech news'),
            ('Programming', 'programming', 'Coding tips'),
            ('Web Development', 'web-development', 'Frontend and backend')");
    }
    if ($conn->query("SELECT COUNT(*) as c FROM blog_posts")->fetch_assoc()['c'] == 0) {
        $conn->query("INSERT INTO blog_posts (title, slug, content, excerpt, featured_image, category_id, status, views) VALUES 
            ('Getting Started with PHP 8', 'getting-started-php-8', '<p>PHP 8 brings exciting features.</p>', 'Discover PHP 8 features', 'https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=800', 2, 'published', 245),
            ('Building REST APIs', 'building-rest-apis', '<p>REST APIs are essential.</p>', 'Learn REST API development', 'https://images.unsplash.com/photo-1627398242454-45a1465c2479?w=800', 3, 'published', 189)");
    }
} else {
    $conn = new mysqli("sql112.infinityfree.com", "if0_40964515", "khan4483com", "if0_40964515_my_portfolio_db");
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
}

// Helper functions
function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function slug($s) { return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($s))); }
function excerpt($text, $length = 150) {
    $text = strip_tags($text);
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $time);
}
?>



