<?php
/**
 * Authentication System - Database & Functions
 * @author Afzal Khan
 */
session_start();

$conn = new mysqli("localhost", "root", "", "auth_system_db");
if ($conn->connect_error) {
    $conn = new mysqli("localhost", "root", "");
    $conn->query("CREATE DATABASE IF NOT EXISTS auth_system_db");
    $conn->select_db("auth_system_db");
    
    $sql = file_get_contents(__DIR__ . '/../database.sql');
    if ($conn->multi_query($sql)) {
        do { if ($r = $conn->store_result()) $r->free(); } 
        while ($conn->more_results() && $conn->next_result());
    }
    $conn->close();
    $conn = new mysqli("localhost", "root", "", "auth_system_db");
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
