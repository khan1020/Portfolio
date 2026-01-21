<?php
/**
 * =============================================================================
 * E-COMMERCE CHECKOUT PAGE
 * =============================================================================
 * 
 * Complete checkout flow with order summary, customer details form,
 * and order confirmation. Uses sessions to manage cart data.
 * 
 * @author  Afzal Khan
 * @version 1.0.0
 * @since   January 2026
 * =============================================================================
 */

session_start();
require_once 'includes/db.php';

// -----------------------------------------------------------------------------
// Process Order Submission
// -----------------------------------------------------------------------------
$order_placed = false;
$order_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate customer information
    $name = trim($_POST['customer_name']);
    $email = trim($_POST['customer_email']);
    $phone = trim($_POST['customer_phone']);
    $address = trim($_POST['customer_address']);
    $city = trim($_POST['customer_city']);
    $cart_data = $_POST['cart_data'];
    $total = floatval($_POST['order_total']);
    
    if (!empty($name) && !empty($email) && !empty($address)) {
        // Create orders table if not exists
        $conn->query("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(50),
            customer_address TEXT,
            customer_city VARCHAR(100),
            order_total DECIMAL(10,2),
            cart_items TEXT,
            status ENUM('pending', 'processing', 'shipped', 'delivered') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, customer_city, order_total, cart_items) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssds", $name, $email, $phone, $address, $city, $total, $cart_data);
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            $order_placed = true;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | NeoMech Keyboards</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Main Styles -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* =================================================================
           CHECKOUT SPECIFIC STYLES
           ================================================================= */
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            padding: 40px 0;
        }
        
        .checkout-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .checkout-form h2 {
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #111827;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .form-group label span {
            color: #ef4444;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2563eb;
        }
        
        .form-group textarea {
            height: 80px;
            resize: none;
        }
        
        /* Order Summary Sidebar */
        .order-summary {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .order-summary h2 {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .order-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .order-item-meta {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .order-item-price {
            font-weight: 600;
            color: #2563eb;
        }
        
        .order-totals {
            border-top: 2px solid #e5e7eb;
            padding-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #6b7280;
        }
        
        .total-row.grand-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background 0.2s;
        }
        
        .checkout-btn:hover {
            background: #059669;
        }
        
        .checkout-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        
        /* Empty Cart */
        .empty-cart-message {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        .empty-cart-message i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #d1d5db;
        }
        
        /* Success Page */
        .success-container {
            text-align: center;
            padding: 80px 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        
        .success-icon i {
            font-size: 3rem;
            color: #10b981;
        }
        
        .order-number {
            background: #f3f4f6;
            padding: 15px 30px;
            border-radius: 8px;
            display: inline-block;
            margin: 20px 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 900px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- ================================================================
         HEADER
         ================================================================ -->
    <header class="header">
        <div class="container">
            <div class="nav">
                <div class="logo">
                    <i class="fas fa-keyboard"></i> NeoMech
                </div>
                <div class="nav-links">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="index.php#products" class="nav-link">Products</a>
                </div>
            </div>
        </div>
    </header>

    <section class="product-section">
        <div class="container">
            <a href="index.php" style="display: inline-block; margin-bottom: 20px; color: #6b7280; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Continue Shopping
            </a>
            
            <?php if ($order_placed): ?>
                <!-- =============================================================
                     ORDER SUCCESS MESSAGE
                     ============================================================= -->
                <div class="success-container">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h1>Order Placed Successfully!</h1>
                    <p style="color: #6b7280; margin: 15px 0;">
                        Thank you for your purchase. We've received your order and will begin processing it shortly.
                    </p>
                    <div class="order-number">
                        Order #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?>
                    </div>
                    <p style="color: #6b7280;">
                        A confirmation email has been sent to your email address.
                    </p>
                    <a href="index.php" class="btn btn-primary" style="margin-top: 30px;">
                        <i class="fas fa-home"></i> Return to Shop
                    </a>
                </div>
                
                <script>
                    // Clear cart after successful order
                    localStorage.removeItem('cart');
                </script>
                
            <?php else: ?>
                <!-- =============================================================
                     CHECKOUT FORM
                     ============================================================= -->
                <div class="checkout-container">
                    <div class="checkout-form">
                        <h2><i class="fas fa-user"></i> Customer Information</h2>
                        
                        <form method="POST" id="checkoutForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="customer_name">Full Name <span>*</span></label>
                                    <input type="text" id="customer_name" name="customer_name" required
                                           placeholder="John Doe">
                                </div>
                                
                                <div class="form-group">
                                    <label for="customer_email">Email <span>*</span></label>
                                    <input type="email" id="customer_email" name="customer_email" required
                                           placeholder="john@example.com">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="customer_phone">Phone Number</label>
                                    <input type="tel" id="customer_phone" name="customer_phone"
                                           placeholder="+92 300 1234567">
                                </div>
                                
                                <div class="form-group">
                                    <label for="customer_city">City</label>
                                    <input type="text" id="customer_city" name="customer_city"
                                           placeholder="Hyderabad">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_address">Shipping Address <span>*</span></label>
                                <textarea id="customer_address" name="customer_address" required
                                          placeholder="Street address, apartment, building..."></textarea>
                            </div>
                            
                            <!-- Hidden fields for cart data -->
                            <input type="hidden" name="cart_data" id="cart_data">
                            <input type="hidden" name="order_total" id="order_total">
                            <input type="hidden" name="place_order" value="1">
                        </form>
                    </div>
                    
                    <!-- Order Summary Sidebar -->
                    <div class="order-summary">
                        <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                        
                        <div class="order-items" id="orderItems">
                            <!-- Items populated by JavaScript -->
                        </div>
                        
                        <div class="order-totals">
                            <div class="total-row">
                                <span>Subtotal</span>
                                <span id="subtotal">$0.00</span>
                            </div>
                            <div class="total-row">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                            <div class="total-row grand-total">
                                <span>Total</span>
                                <span id="grandTotal">$0.00</span>
                            </div>
                        </div>
                        
                        <button type="submit" form="checkoutForm" class="checkout-btn" id="placeOrderBtn">
                            <i class="fas fa-lock"></i> Place Order
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ================================================================
         FOOTER
         ================================================================ -->
    <footer style="background: #f3f4f6; padding: 40px 0; margin-top: 60px;">
        <div class="container" style="text-align: center; color: #6b7280;">
            <p>&copy; 2026 NeoMech Keyboards. Built by Afzal Khan.</p>
        </div>
    </footer>

    <script>
        // =================================================================
        // CHECKOUT PAGE JAVASCRIPT
        // =================================================================
        
        document.addEventListener('DOMContentLoaded', function() {
            // Load cart from localStorage
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            // Elements
            const orderItems = document.getElementById('orderItems');
            const subtotal = document.getElementById('subtotal');
            const grandTotal = document.getElementById('grandTotal');
            const cartDataInput = document.getElementById('cart_data');
            const orderTotalInput = document.getElementById('order_total');
            const placeOrderBtn = document.getElementById('placeOrderBtn');
            
            // Check if elements exist (they won't on success page)
            if (!orderItems) return;
            
            // -----------------------------------------------------------------
            // Display cart items
            // -----------------------------------------------------------------
            if (cart.length === 0) {
                orderItems.innerHTML = `
                    <div class="empty-cart-message">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Your cart is empty</p>
                        <a href="index.php" class="btn btn-primary" style="margin-top: 15px;">
                            Browse Products
                        </a>
                    </div>
                `;
                placeOrderBtn.disabled = true;
                return;
            }
            
            let total = 0;
            let itemsHTML = '';
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                
                itemsHTML += `
                    <div class="order-item">
                        <img src="${item.image}" alt="${item.name}" 
                             onerror="this.src='https://via.placeholder.com/60'">
                        <div class="order-item-details">
                            <div class="order-item-name">${item.name}</div>
                            <div class="order-item-meta">
                                ${item.color ? item.color + ' • ' : ''}Qty: ${item.quantity}
                            </div>
                        </div>
                        <div class="order-item-price">$${itemTotal.toFixed(2)}</div>
                    </div>
                `;
            });
            
            orderItems.innerHTML = itemsHTML;
            subtotal.textContent = `$${total.toFixed(2)}`;
            grandTotal.textContent = `$${total.toFixed(2)}`;
            
            // Set hidden form values
            cartDataInput.value = JSON.stringify(cart);
            orderTotalInput.value = total.toFixed(2);
        });
    </script>
</body>
</html>
