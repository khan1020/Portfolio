<?php
/**
 * Stripe Payment Gateway Demo
 * @author Afzal Khan
 */
session_start();

$products = [
    ['id' => 1, 'name' => 'Pro Plan', 'price' => 29.99, 'desc' => 'Perfect for professionals', 'features' => ['Unlimited projects', 'Priority support', 'Advanced analytics']],
    ['id' => 2, 'name' => 'Team Plan', 'price' => 79.99, 'desc' => 'Best for growing teams', 'features' => ['Everything in Pro', 'Team collaboration', 'Admin dashboard']],
    ['id' => 3, 'name' => 'Enterprise', 'price' => 199.99, 'desc' => 'For large organizations', 'features' => ['Everything in Team', 'Custom integrations', 'Dedicated support']],
];

$page = isset($_GET['page']) ? $_GET['page'] : 'products';
$selectedProduct = null;

if ($page === 'checkout' && isset($_GET['product'])) {
    foreach ($products as $p) {
        if ($p['id'] == $_GET['product']) $selectedProduct = $p;
    }
    if (!$selectedProduct) $page = 'products';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    // In production, process with Stripe API here
    $_SESSION['order'] = [
        'product' => $selectedProduct,
        'email' => $_POST['email'],
        'name' => $_POST['name']
    ];
    header("Location: index.php?page=success");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayFlow - Secure Payments</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 40px; color: white; }
        .header h1 { font-size: 2.5rem; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 15px; }
        .header p { opacity: 0.9; }
        .pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 30px; }
        .pricing-card { background: white; border-radius: 20px; padding: 35px; text-align: center; transition: transform 0.3s; }
        .pricing-card:hover { transform: translateY(-10px); }
        .pricing-card.popular { border: 3px solid #667eea; position: relative; }
        .popular-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white; padding: 5px 20px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .pricing-card h3 { font-size: 1.5rem; margin-bottom: 10px; }
        .pricing-card .price { font-size: 3rem; font-weight: 700; color: #667eea; margin: 20px 0; }
        .pricing-card .price span { font-size: 1rem; color: #6b7280; }
        .pricing-card p { color: #6b7280; margin-bottom: 25px; }
        .pricing-card ul { list-style: none; text-align: left; margin-bottom: 30px; }
        .pricing-card li { padding: 10px 0; color: #374151; display: flex; align-items: center; gap: 10px; }
        .pricing-card li i { color: #10b981; }
        .btn { display: block; width: 100%; padding: 15px; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; transition: all 0.2s; }
        .btn-primary { background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: scale(1.02); box-shadow: 0 10px 20px rgba(102,126,234,0.3); }
        .checkout-container { max-width: 500px; margin: 0 auto; }
        .checkout-card { background: white; border-radius: 20px; padding: 40px; }
        .checkout-card h2 { margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
        .order-summary { background: #f9fafb; padding: 20px; border-radius: 12px; margin-bottom: 30px; }
        .order-summary h4 { margin-bottom: 10px; }
        .order-summary .total { display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 700; color: #667eea; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input { width: 100%; padding: 15px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 1rem; }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .card-input { display: grid; grid-template-columns: 1fr 80px 80px; gap: 10px; }
        .secure-badge { display: flex; align-items: center; justify-content: center; gap: 10px; color: #6b7280; font-size: 0.9rem; margin-top: 20px; }
        .success-container { text-align: center; max-width: 500px; margin: 0 auto; }
        .success-card { background: white; border-radius: 20px; padding: 50px; }
        .success-icon { width: 100px; height: 100px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; }
        .success-icon i { font-size: 3rem; color: #10b981; }
        .back-link { display: block; text-align: center; margin-top: 30px; color: rgba(255,255,255,0.8); text-decoration: none; }
        @media (max-width: 768px) { .pricing-grid { grid-template-columns: 1fr; } .card-input { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($page === 'products'): ?>
            <div class="header">
                <h1><i class="fas fa-credit-card"></i> PayFlow</h1>
                <p>Secure payment processing demo with Stripe integration</p>
            </div>
            
            <div class="pricing-grid">
                <?php foreach ($products as $i => $product): ?>
                    <div class="pricing-card <?php echo $i === 1 ? 'popular' : ''; ?>">
                        <?php if ($i === 1): ?><span class="popular-badge">Most Popular</span><?php endif; ?>
                        <h3><?php echo $product['name']; ?></h3>
                        <div class="price">$<?php echo $product['price']; ?><span>/month</span></div>
                        <p><?php echo $product['desc']; ?></p>
                        <ul>
                            <?php foreach ($product['features'] as $f): ?>
                                <li><i class="fas fa-check-circle"></i> <?php echo $f; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="index.php?page=checkout&product=<?php echo $product['id']; ?>" class="btn btn-primary">Select Plan</a>
                    </div>
                <?php endforeach; ?>
            </div>
            
        <?php elseif ($page === 'checkout' && $selectedProduct): ?>
            <div class="checkout-container">
                <a href="index.php" style="color:white; text-decoration:none; display:block; margin-bottom:20px;"><i class="fas fa-arrow-left"></i> Back</a>
                <div class="checkout-card">
                    <h2><i class="fas fa-lock"></i> Secure Checkout</h2>
                    
                    <div class="order-summary">
                        <h4>Order Summary</h4>
                        <p><?php echo $selectedProduct['name']; ?> - <?php echo $selectedProduct['desc']; ?></p>
                        <div class="total">
                            <span>Total</span>
                            <span>$<?php echo $selectedProduct['price']; ?></span>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" required placeholder="you@example.com">
                        </div>
                        <div class="form-group">
                            <label>Name on Card</label>
                            <input type="text" name="name" required placeholder="John Doe">
                        </div>
                        <div class="form-group">
                            <label>Card Details</label>
                            <div class="card-input">
                                <input type="text" placeholder="4242 4242 4242 4242" maxlength="19">
                                <input type="text" placeholder="MM/YY" maxlength="5">
                                <input type="text" placeholder="CVC" maxlength="4">
                            </div>
                        </div>
                        <button type="submit" name="pay" class="btn btn-primary">
                            <i class="fas fa-lock"></i> Pay $<?php echo $selectedProduct['price']; ?>
                        </button>
                        <div class="secure-badge">
                            <i class="fas fa-shield-alt" style="color:#10b981;"></i> Secured by Stripe
                        </div>
                    </form>
                </div>
            </div>
            
        <?php elseif ($page === 'success'): ?>
            <div class="success-container">
                <div class="success-card">
                    <div class="success-icon"><i class="fas fa-check"></i></div>
                    <h2>Payment Successful!</h2>
                    <p style="color:#6b7280; margin: 20px 0;">Thank you for your purchase. A confirmation email has been sent.</p>
                    <?php if (isset($_SESSION['order'])): ?>
                        <p><strong>Plan:</strong> <?php echo $_SESSION['order']['product']['name']; ?></p>
                        <p><strong>Amount:</strong> $<?php echo $_SESSION['order']['product']['price']; ?></p>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-primary" style="margin-top:30px;"><i class="fas fa-home"></i> Back to Plans</a>
                </div>
            </div>
        <?php endif; ?>
        
        <a href="../../index.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
    </div>
</body>
</html>
