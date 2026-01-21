<?php
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "ecommerce_db";

// Create connection
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

// Check tables (auto-setup if missing - simple check)
$checkTable = "SHOW TABLES LIKE 'products'";
if ($conn->query($checkTable)->num_rows == 0) {
    // Run the SQL from the file if table doesn't exist
    $sqlContent = file_get_contents(__DIR__ . '/../database.sql');
    if ($conn->multi_query($sqlContent)) {
        do {
            // consume results to clear stack
            if ($res = $conn->store_result()) $res->free();
        } while ($conn->more_results() && $conn->next_result());
    }
}
?>
