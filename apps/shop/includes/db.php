<?php
$servername = "sql112.infinityfree.com";
$username = "if0_40964515";
$password = "khan4483com";
$dbname = "if0_40964515_my_portfolio_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
