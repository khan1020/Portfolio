<?php require_once 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeoMech Keyboards - Premium Mechanical</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav">
                <div class="logo">
                    <i class="fas fa-keyboard"></i> NeoMech
                </div>
                <div class="nav-links">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="#" class="nav-link">Keyboards</a>
                    <a href="#" class="nav-link">Keycaps</a>
                    <a href="admin/add-product.php" class="nav-link" style="color: #2563eb;"><i class="fas fa-cog"></i> Admin</a>
                </div>
                <div class="cart-icon" id="cartIcon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section style="background: #111827; color: white; padding: 80px 0; text-align: center;">
        <div class="container">
            <h1 style="font-size: 3.5rem; margin-bottom: 20px;">Typing Nirvana</h1>
            <p style="font-size: 1.25rem; color: #9ca3af; max-width: 600px; margin: 0 auto 30px;">
                Hand-crafted mechanical keyboards and artisan keycaps for the ultimate typing experience.
            </p>
            <a href="#products" class="btn btn-primary">Shop Collection</a>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="product-section" id="products">
        <div class="container">
            <h2 style="font-size: 2rem; margin-bottom: 40px; text-align: center;">Featured Collection</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
                <?php
                $sql = "SELECT * FROM products ORDER BY id DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '
                        <div class="product-card" style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.3s;">
                            <a href="product.php?id='.$row['id'].'" style="text-decoration: none; color: inherit;">
                                <img src="'.$row['image_url'].'" alt="'.$row['name'].'" style="width: 100%; height: 250px; object-fit: cover;">
                                <div style="padding: 20px;">
                                    <h3 style="font-size: 1.25rem; margin-bottom: 10px;">'.$row['name'].'</h3>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-weight: 700; color: #2563eb; font-size: 1.1rem;">$'.$row['price'].'</span>
                                        <button class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem;">View Details</button>
                                    </div>
                                </div>
                            </a>
                        </div>
                        ';
                    }
                } else {
                    echo '<p style="text-align: center; grid-column: 1/-1;">No products found. Add some in the Admin Panel!</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: #f3f4f6; padding: 40px 0; margin-top: 60px;">
        <div class="container" style="text-align: center; color: #6b7280;">
            <p>&copy; 2026 NeoMech Keyboards. Built by Afzal Khan.</p>
        </div>
    </footer>
    
    <script src="js/app.js"></script>
</body>
</html>
