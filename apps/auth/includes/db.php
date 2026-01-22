<?php
session_start();

$conn = new mysqli("sql112.infinityfree.com", "if0_40964515", "khan4483com", "if0_40964515_my_portfolio_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    $id = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $id");
    return $result->fetch_assoc();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireGuest() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit;
    }
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}
?>
