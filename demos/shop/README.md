# NeoMech E-commerce Product Page

A modern, fully-functional e-commerce product showcase built with PHP, MySQL, and vanilla JavaScript.

## 🌟 Features

### Customer Features
- **Product Listing** - Browse all products with images and prices
- **Product Details** - Detailed view with options (switch types, quantity)
- **Shopping Cart** - Add/remove items with localStorage persistence
- **Checkout Flow** - Complete order form with validation
- **Order Confirmation** - Order number and confirmation display

### Admin Features
- **Product Management** - Add, edit, and delete products
- **Stock Tracking** - Visual stock level indicators
- **Order Viewing** - Orders saved to database

## 🚀 Quick Start

### Prerequisites
- XAMPP (or any PHP/MySQL environment)
- Apache & MySQL running

### Installation

1. **Copy files** to your web directory:
   ```
   C:\xampp\htdocs\Portfolio\demos\01-ecommerce-product\
   ```

2. **Start XAMPP** - Enable Apache and MySQL

3. **Visit the site**:
   ```
   http://localhost/Portfolio/demos/01-ecommerce-product/
   ```

4. **Database auto-creates** on first visit with sample products

## 📁 Project Structure

```
01-ecommerce-product/
├── index.php          # Homepage with product grid
├── product.php        # Individual product view
├── checkout.php       # Checkout & order confirmation
├── database.sql       # Database schema + sample data
├── README.md          # This file
│
├── admin/
│   └── index.php      # Full admin panel (CRUD)
│
├── includes/
│   └── db.php         # Database connection (auto-setup)
│
├── css/
│   └── style.css      # All styles with CSS variables
│
├── js/
│   └── app.js         # Cart, gallery, notifications
│
└── images/            # Product images (uses Unsplash URLs)
```

## 💾 Database Schema

### Products Table
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment ID |
| name | VARCHAR(255) | Product name |
| description | TEXT | Product details |
| price | DECIMAL(10,2) | Price in USD |
| image_url | VARCHAR(255) | Image URL |
| stock | INT | Stock quantity |
| created_at | TIMESTAMP | Creation date |

### Orders Table
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Order number |
| customer_name | VARCHAR(255) | Customer name |
| customer_email | VARCHAR(255) | Email address |
| customer_phone | VARCHAR(50) | Phone number |
| customer_address | TEXT | Shipping address |
| customer_city | VARCHAR(100) | City |
| order_total | DECIMAL(10,2) | Total amount |
| cart_items | TEXT | JSON cart data |
| status | ENUM | pending/processing/shipped/delivered |
| created_at | TIMESTAMP | Order date |

## 🎨 Customization

### Changing Theme Colors
Edit `css/style.css`:
```css
:root {
    --primary-color: #2563eb;    /* Main blue */
    --secondary-color: #1e40af;  /* Darker blue */
    --success-color: #10b981;    /* Green */
    --danger-color: #ef4444;     /* Red */
}
```

### Adding New Products
1. Go to Admin Panel: `/admin/index.php`
2. Fill in product details
3. Use Unsplash URLs for images

## 🔧 API Reference

### JavaScript Functions

| Function | Description |
|----------|-------------|
| `addToCart()` | Add current product to cart |
| `removeFromCart(index)` | Remove item by index |
| `toggleCart()` | Open/close cart sidebar |
| `updateCartCount()` | Update header badge |
| `showNotification(msg)` | Show toast message |
| `proceedToCheckout()` | Navigate to checkout |

## 📝 Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: Vanilla JavaScript (ES6+)
- **Styling**: CSS3 with Grid/Flexbox
- **Icons**: Font Awesome 6.5
- **Fonts**: Google Fonts (Inter)

## 📄 License

Built by **Afzal Khan** | January 2026
Part of the Full Stack Developer Portfolio

---

## Screenshots

### Product Listing
![Products](https://via.placeholder.com/600x400?text=Product+Grid)

### Admin Panel
![Admin](https://via.placeholder.com/600x400?text=Admin+Panel)

### Checkout
![Checkout](https://via.placeholder.com/600x400?text=Checkout+Flow)
