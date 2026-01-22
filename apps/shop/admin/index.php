<?php
/**
 * =============================================================================
 * E-COMMERCE ADMIN PANEL - PRODUCT MANAGEMENT
 * =============================================================================
 * 
 * This file provides a complete admin interface for managing products:
 * - View all products in a table
 * - Add new products
 * - Edit existing products
 * - Delete products
 * 
 * @author  Afzal Khan
 * @version 1.0.0
 * @since   January 2026
 * =============================================================================
 */

require_once '../includes/db.php';

// -----------------------------------------------------------------------------
// Handle Delete Request
// -----------------------------------------------------------------------------
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_message = "Product deleted successfully!";
    } else {
        $error_message = "Error deleting product: " . $conn->error;
    }
    $stmt->close();
}

// -----------------------------------------------------------------------------
// Handle Add/Edit Product Form Submission
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $image_url = trim($_POST['image_url']);
    $stock = (int)$_POST['stock'];
    
    // Validate required fields
    if (empty($name) || $price <= 0) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Determine if this is an update or insert
        if (!empty($_POST['product_id'])) {
            // UPDATE existing product
            $id = (int)$_POST['product_id'];
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image_url=?, stock=? WHERE id=?");
            $stmt->bind_param("ssdsis", $name, $description, $price, $image_url, $stock, $id);
            $action = "updated";
        } else {
            // INSERT new product
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, image_url, stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsi", $name, $description, $price, $image_url, $stock);
            $action = "added";
        }
        
        if ($stmt->execute()) {
            $success_message = "Product {$action} successfully!";
        } else {
            $error_message = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// -----------------------------------------------------------------------------
// Fetch product for editing (if edit mode)
// -----------------------------------------------------------------------------
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM products WHERE id = $edit_id");
    if ($result->num_rows > 0) {
        $edit_product = $result->fetch_assoc();
    }
}

// -----------------------------------------------------------------------------
// Fetch all products for the table
// -----------------------------------------------------------------------------
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - NeoMech Keyboards</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* =================================================================
           CSS VARIABLES - Easy theme customization
           ================================================================= */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-900: #111827;
        }
        
        /* =================================================================
           BASE STYLES
           ================================================================= */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-100);
            color: var(--gray-900);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* =================================================================
           HEADER
           ================================================================= */
        .header {
            background: var(--gray-900);
            color: white;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .back-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
            transition: background 0.2s;
        }
        
        .back-link:hover { background: rgba(255,255,255,0.2); }
        
        /* =================================================================
           ALERTS - Success/Error Messages
           ================================================================= */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        /* =================================================================
           CARDS - Reusable card component
           ================================================================= */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .card-title {
            font-size: 1.25rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray-900);
        }
        
        /* =================================================================
           FORM STYLES
           ================================================================= */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: var(--gray-600);
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            color: var(--gray-600);
            font-size: 0.875rem;
        }
        
        /* =================================================================
           BUTTONS
           ================================================================= */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        
        .btn-group {
            display: flex;
            gap: 8px;
        }
        
        /* =================================================================
           TABLE STYLES
           ================================================================= */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }
        
        th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-600);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background: var(--gray-50);
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .price-badge {
            background: #dbeafe;
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .stock-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .stock-in { background: #d1fae5; color: #065f46; }
        .stock-low { background: #fef3c7; color: #92400e; }
        .stock-out { background: #fee2e2; color: #991b1b; }
        
        /* =================================================================
           RESPONSIVE DESIGN
           ================================================================= */
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .header-content { flex-direction: column; gap: 15px; text-align: center; }
            .btn-group { flex-direction: column; }
        }
    </style>
</head>
<body>
    <!-- ================================================================
         HEADER SECTION
         ================================================================ -->
    <header class="header">
        <div class="header-content">
            <h1><i class="fas fa-cog"></i> Admin Panel - NeoMech Keyboards</h1>
            <a href="../index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Store
            </a>
        </div>
    </header>

    <div class="container">
        <!-- =============================================================
             ALERT MESSAGES
             ============================================================= -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- =============================================================
             ADD/EDIT PRODUCT FORM
             ============================================================= -->
        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-<?php echo $edit_product ? 'edit' : 'plus-circle'; ?>"></i>
                <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
            </h2>
            
            <form method="POST" action="index.php">
                <!-- Hidden field for edit mode -->
                <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" required
                               placeholder="e.g., CyberDeck 2077"
                               value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required
                               placeholder="199.99"
                               value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock Quantity</label>
                        <input type="number" id="stock" name="stock" min="0"
                               placeholder="10"
                               value="<?php echo $edit_product ? $edit_product['stock'] : '10'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input type="url" id="image_url" name="image_url"
                               placeholder="https://images.unsplash.com/..."
                               value="<?php echo $edit_product ? htmlspecialchars($edit_product['image_url']) : ''; ?>">
                        <small>Use Unsplash or any hosted image URL</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"
                                  placeholder="Product details and specifications..."><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                    </button>
                    
                    <?php if ($edit_product): ?>
                        <a href="index.php" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- =============================================================
             PRODUCTS TABLE
             ============================================================= -->
        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-box"></i> All Products
                <span style="margin-left: auto; font-size: 0.9rem; color: var(--gray-600);">
                    <?php echo $products->num_rows; ?> items
                </span>
            </h2>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products->num_rows > 0): ?>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="product-image"
                                             onerror="this.src='https://via.placeholder.com/60'">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <br>
                                        <small style="color: var(--gray-600);">
                                            <?php echo substr(htmlspecialchars($product['description']), 0, 50); ?>...
                                        </small>
                                    </td>
                                    <td>
                                        <span class="price-badge">$<?php echo number_format($product['price'], 2); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $stock = $product['stock'];
                                        $stockClass = $stock > 5 ? 'stock-in' : ($stock > 0 ? 'stock-low' : 'stock-out');
                                        $stockText = $stock > 5 ? "In Stock ({$stock})" : ($stock > 0 ? "Low ({$stock})" : "Out of Stock");
                                        ?>
                                        <span class="stock-badge <?php echo $stockClass; ?>">
                                            <?php echo $stockText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="index.php?edit=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?delete=<?php echo $product['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this product?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: var(--gray-600);">
                                    <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                                    No products found. Add your first product above!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================================================================
         FOOTER
         ================================================================ -->
    <footer style="text-align: center; padding: 20px; color: var(--gray-600);">
        <p>&copy; 2026 NeoMech Admin Panel | Built by Afzal Khan</p>
    </footer>
</body>
</html>
