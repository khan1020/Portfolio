<?php
/**
 * Database Connection - Blog CMS
 * @author Afzal Khan
 * @since January 2026
 */

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blog_cms_db";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Auto-setup tables
if ($conn->query("SHOW TABLES LIKE 'posts'")->num_rows == 0) {
    $sql = file_get_contents(__DIR__ . '/../database.sql');
    if ($conn->multi_query($sql)) {
        do { if ($res = $conn->store_result()) $res->free(); } 
        while ($conn->more_results() && $conn->next_result());
    }
    $conn->close();
    $conn = new mysqli($servername, $username, $password, $dbname);
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
