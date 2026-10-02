<?php
// Smart database connection
session_start();
$isLocal = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);

if ($isLocal) {
    $conn = new mysqli("localhost", "root", "");
    $conn->query("CREATE DATABASE IF NOT EXISTS auth_system_db");
    $conn->select_db("auth_system_db");
    
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        avatar VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    if ($conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'] == 0) {
        $conn->query("INSERT INTO users (username, email, password, full_name) VALUES 
            ('demo', 'demo@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo User')");
    }
} else {
    $conn = new mysqli("sql112.infinityfree.com", "if0_40964515", "khan4483com", "if0_40964515_my_portfolio_db");
    if ($conn->connect_error) die("Connection failed");
}

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function isLoggedIn() { return isset($_SESSION['user_id']); }
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    $id = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $id");
    return $result->fetch_assoc();
}
function requireLogin() { if (!isLoggedIn()) { header('Location: login.php'); exit; } }
function requireGuest() { if (isLoggedIn()) { header('Location: dashboard.php'); exit; } }
function generateToken($length = 32) { return bin2hex(random_bytes($length)); }
?>

