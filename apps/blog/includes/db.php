<?php
$servername = "sql112.infinityfree.com";
$username = "if0_40964515";
$password = "khan4483com";
$dbname = "if0_40964515_my_portfolio_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

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
