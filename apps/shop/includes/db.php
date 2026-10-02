<?php
// Smart database connection - works both locally and on InfinityFree
$isLocal = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']) || 
           strpos($_SERVER['SERVER_NAME'], '.local') !== false;

if ($isLocal) {
    // Local XAMPP settings
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "ecommerce_db";
    
    $conn = new mysqli($servername, $username, $password);
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
    
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->select_db($dbname);
    
    // Auto-create tables if not exist
    $conn->query("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image_url VARCHAR(255),
        stock INT DEFAULT 10,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add sample data if empty
    if ($conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'] == 0) {
        $conn->query("INSERT INTO products (name, description, price, image_url, stock) VALUES 
            ('CyberDeck 2077', 'Split ergonomic mechanical keyboard', 299.99, 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=600', 5),
            ('Zenith 65% Brass', 'Heavy brass weight keyboard kit', 189.50, 'https://images.unsplash.com/photo-1626218174397-91aee348c4cf?w=600', 10),
            ('Nebula Resin Keycaps', 'Artisan keycap set with galaxy swirls', 85.00, 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=600', 20),
            ('Stealth Ops Deskmat', 'Water-resistant cloth deskmat', 24.99, 'https://images.unsplash.com/photo-1615663245857-acda847f842b?w=600', 50)");
    }
} else {
    // InfinityFree settings
    $conn = new mysqli("sql112.infinityfree.com", "if0_40964515", "khan4483com", "if0_40964515_my_portfolio_db");
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
}
?>

