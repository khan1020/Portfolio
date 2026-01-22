<?php
/**
 * =============================================================================
 * DATABASE CONNECTION - Task Manager
 * =============================================================================
 * 
 * Handles database connection and auto-setup for the Task Manager.
 * Creates database and tables automatically on first run.
 * 
 * @author  Afzal Khan
 * @version 1.0.0
 * @since   January 2026
 * =============================================================================
 */

// Database configuration
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "task_manager_db";

// Create connection (without selecting database first)
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    $conn->select_db($dbname);
} else {
    die("Error creating database: " . $conn->error);
}

// Check if tables exist, if not run the SQL file
$checkTable = "SHOW TABLES LIKE 'tasks'";
$result = $conn->query($checkTable);

if ($result->num_rows == 0) {
    // Tables don't exist, create them
    $sqlFile = file_get_contents(__DIR__ . '/../database.sql');
    
    // Execute multi-query
    if ($conn->multi_query($sqlFile)) {
        do {
            // Consume all results
            if ($res = $conn->store_result()) {
                $res->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    }
    
    // Reconnect after multi_query
    $conn->close();
    $conn = new mysqli($servername, $username, $password, $dbname);
}

/**
 * Helper function to safely escape output
 * @param string $str - String to escape
 * @return string Escaped string
 */
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Get priority badge class
 * @param string $priority - Priority level
 * @return string CSS class
 */
function getPriorityClass($priority) {
    switch ($priority) {
        case 'high': return 'priority-high';
        case 'medium': return 'priority-medium';
        case 'low': return 'priority-low';
        default: return 'priority-medium';
    }
}

/**
 * Get status badge class
 * @param string $status - Status value
 * @return string CSS class
 */
function getStatusClass($status) {
    switch ($status) {
        case 'completed': return 'status-completed';
        case 'in_progress': return 'status-progress';
        case 'pending': return 'status-pending';
        default: return 'status-pending';
    }
}

/**
 * Format date for display
 * @param string $date - Date string
 * @return string Formatted date
 */
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
