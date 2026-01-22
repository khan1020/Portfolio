<?php
$servername = "sql112.infinityfree.com";
$username = "if0_40964515";
$password = "khan4483com";
$dbname = "if0_40964515_my_portfolio_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function getPriorityClass($priority) {
    switch ($priority) {
        case 'high': return 'priority-high';
        case 'medium': return 'priority-medium';
        case 'low': return 'priority-low';
        default: return 'priority-medium';
    }
}

function getStatusClass($status) {
    switch ($status) {
        case 'completed': return 'status-completed';
        case 'in_progress': return 'status-progress';
        case 'pending': return 'status-pending';
        default: return 'status-pending';
    }
}

function formatDate($date) {
    if (empty($date)) return 'No due date';
    $timestamp = strtotime($date);
    $today = strtotime('today');
    $tomorrow = strtotime('tomorrow');
    
    if ($timestamp == $today) return 'Today';
    if ($timestamp == $tomorrow) return 'Tomorrow';
    if ($timestamp < $today) return 'Overdue';
    
    return date('M j, Y', $timestamp);
}
?>
