<?php 
require_once 'includes/db.php'; 

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $conn->real_escape_string($_GET['id']);
$sql = "SELECT * FROM products WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Product not found");
}

$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> | NeoMech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav">
                <div class="logo"><i class="fas fa-keyboard"></i> NeoMech</div>
                <div class="nav-links">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="#" class="nav-link">About</a>
                    <a href="admin/add-product.php" class="nav-link" style="color: #2563eb;">Admin Panel</a>
                </div>
                <div class="cart-icon" id="cartIcon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </div>
            </div>
        </div>
    </header>

    <section class="product-section">
        <div class="container">
            <a href="index.php" style="display: inline-block; margin-bottom: 20px; color: #6b7280; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Products</a>
            
            <div class="product-container">
                <!-- Image -->
                <div class="product-gallery">
                    <div class="main-image">
                        <img id="mainImage" src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                </div>

                <!-- Details -->
                <div class="product-details">
                    <div class="product-category">Custom Mechanical</div>
                    <h1 class="product-title"><?php echo $product['name']; ?></h1>
                    
                    <div class="product-price">
                        <span class="current-price">$<?php echo $product['price']; ?></span>
                        <span class="stock-badge" style="background: #10b981; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; margin-left: 10px;">In Stock</span>
                    </div>

                    <p class="product-description">
                        <?php echo $product['description']; ?>
                    </p>

                    <div class="product-options">
                        <div class="option-group">
                            <label>Switch Type:</label>
                            <select id="switchType" style="padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; width: 100%;">
                                <option>Cherry MX Red (Linear)</option>
                                <option>Cherry MX Blue (Clicky)</option>
                                <option>Cherry MX Brown (Tactile)</option>
                                <option>Gateron Yellow (Smooth)</option>
                            </select>
                        </div>

                        <div class="option-group">
                            <label>Quantity:</label>
                            <div class="quantity-selector">
                                <button class="qty-btn" onclick="decreaseQty()">-</button>
                                <input type="number" id="quantity" value="1" min="1" max="10" readonly>
                                <button class="qty-btn" onclick="increaseQty()">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="product-actions">
                        <button class="btn btn-primary" onclick="addToCartDynamic(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo $product['image_url']; ?>')">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h2>Shopping Cart</h2>
            <button class="close-cart" onclick="toggleCart()"><i class="fas fa-times"></i></button>
        </div>
        <div class="cart-items" id="cartItems"></div>
        <div class="cart-footer">
            <div class="cart-total"><span>Total:</span> <span id="cartTotal">$0.00</span></div>
            <button class="btn btn-primary btn-block" onclick="proceedToCheckout()">
                <i class="fas fa-lock"></i> Checkout
            </button>
        </div>
    </div>
    <div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>

    <script src="js/app.js"></script>
    <script>
        function addToCartDynamic(id, name, price, image) {
            const quantity = parseInt(document.getElementById('quantity').value);
            const switchType = document.getElementById('switchType').value;
            
            const cartItem = {
                id: id,
                name: name,
                price: price,
                color: switchType, // Reusing 'color' field for switch type
                quantity: quantity,
                image: image,
                subtotal: price * quantity
            };
            
            // Add to cart logic
            const existingItem = cart.find(item => item.id === id && item.color === switchType);
            if (existingItem) {
                existingItem.quantity += quantity;
                existingItem.subtotal = existingItem.price * existingItem.quantity;
            } else {
                cart.push(cartItem);
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            updateCartDisplay();
            toggleCart();
            showNotification('Product added to cart!');
        }
    </script>
</body>
</html>
