<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeoMech Admin - Add Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; padding: 40px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #111827; margin-bottom: 25px; font-size: 1.5rem; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; }
        textarea { height: 100px; resize: vertical; }
        button { width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #1d4ed8; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #6b7280; text-decoration: none; }
        .back-link:hover { color: #2563eb; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-plus-circle"></i> Add New Product</h1>
        
        <?php
        require_once '../includes/db.php';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $conn->real_escape_string($_POST['name']);
            $price = $conn->real_escape_string($_POST['price']);
            $desc = $conn->real_escape_string($_POST['description']);
            $image_url = $conn->real_escape_string($_POST['image_url']);
            
            // Allow file upload later, simple URL for now to keep it fast
            $sql = "INSERT INTO products (name, description, price, image_url) VALUES ('$name', '$desc', '$price', '$image_url')";
            
            if ($conn->query($sql)) {
                echo '<div class="alert alert-success">Product added successfully!</div>';
            } else {
                echo '<div class="alert alert-error">Error: ' . $conn->error . '</div>';
            }
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" required placeholder="e.g., CyberDeck 2077">
            </div>
            
            <div class="form-group">
                <label>Price ($)</label>
                <input type="number" step="0.01" name="price" required placeholder="199.99">
            </div>
            
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="image_url" required placeholder="https://example.com/image.jpg">
                <small style="color: #6b7280; display: block; margin-top: 5px;">Use Unsplash URL or hosted image link</small>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required placeholder="Product details..."></textarea>
            </div>
            
            <button type="submit">Add Product</button>
        </form>
        
        <a href="../index.php" class="back-link">Back to Store</a>
    </div>
</body>
</html>
