<?php
/**
 * Weather API Proxy
 * Hides API key from client-side code
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// IMPORTANT: Get your own free API key at: https://openweathermap.org/api
// This demo key may be rate-limited
// $API_KEY = 'f9b4a8c4e8d6c5f7a3b2d1e9f8c7b6a5'; // Replace with your key
$API_KEY = '5c2388cf24d98714bc6bff5ba855e991'; 
$BASE_URL = 'https://api.openweathermap.org/data/2.5';

$type = $_GET['type'] ?? 'weather';
$city = $_GET['city'] ?? '';
$lat = $_GET['lat'] ?? '';
$lon = $_GET['lon'] ?? '';

if (empty($city) && (empty($lat) || empty($lon))) {
    http_response_code(400);
    echo json_encode(['error' => 'City or coordinates required']);
    exit;
}

// Build API URL
if (!empty($city)) {
    $url = "$BASE_URL/$type?q=" . urlencode($city) . "&units=metric&appid=$API_KEY";
} else {
    $url = "$BASE_URL/$type?lat=$lat&lon=$lon&units=metric&appid=$API_KEY";
}

// Fetch data
$response = @file_get_contents($url);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch weather data']);
    exit;
}

echo $response;
?>

