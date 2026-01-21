CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    stock INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some dummy data to start with (The "NeoMech" Theme)
INSERT INTO products (name, description, price, image_url, stock) VALUES 
('CyberDeck 2077', 'A split ergonomic mechanical keyboard with OLED displays and rotary encoders. Perfect for the cyberpunk aesthetic.', 299.99, 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=600&h=600&fit=crop', 5),
('Zenith 65% Brass', 'Heavy brass weight, gasket mounted 65% keyboard kit. Anodized aluminum case in Deep Navy.', 189.50, 'https://images.unsplash.com/photo-1626218174397-91aee348c4cf?w=600&h=600&fit=crop', 10),
('Nebula Resin Keycaps', 'Hand-cast artisan keycap set with galaxy swirls and gold flakes. Cherry profile, 120 keys.', 85.00, 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=600&h=600&fit=crop', 20),
('Stealth Ops Deskmat', 'Water-resistant, high-density cloth deskmat. 900x400mm. Stealth black geometric pattern.', 24.99, 'https://images.unsplash.com/photo-1615663245857-acda847f842b?w=600&h=600&fit=crop', 50);
