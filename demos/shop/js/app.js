/**
 * =============================================================================
 * NEOMECH E-COMMERCE - MAIN JAVASCRIPT
 * =============================================================================
 * 
 * This file handles all client-side functionality for the e-commerce store:
 * - Shopping cart management (add, remove, update)
 * - Image gallery interactions
 * - Cart sidebar toggle
 * - Notification system
 * - Local storage persistence
 * 
 * @author  Afzal Khan
 * @version 1.0.0
 * @since   January 2026
 * =============================================================================
 */

// =============================================================================
// GLOBAL STATE
// =============================================================================

/**
 * Default product data for static pages
 * Note: Dynamic pages use PHP to inject product data
 */
const product = {
    name: "Premium Wireless Headphones",
    price: 149.99,
    originalPrice: 199.99,
    selectedColor: "Black",
    image: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&h=600&fit=crop"
};

/**
 * Shopping cart array
 * Persisted to localStorage for session continuity
 */
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// =============================================================================
// INITIALIZATION
// =============================================================================

/**
 * Initialize the page when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    setupColorSelection();
    updateCartDisplay();
});

// =============================================================================
// IMAGE GALLERY FUNCTIONS
// =============================================================================

/**
 * Change the main product image when thumbnail is clicked
 * @param {HTMLElement} thumbnail - The clicked thumbnail element
 */
function changeImage(thumbnail) {
    const mainImage = document.getElementById('mainImage');
    const thumbnails = document.querySelectorAll('.thumbnail');

    // Remove active class from all thumbnails
    thumbnails.forEach(thumb => thumb.classList.remove('active'));

    // Add active class to clicked thumbnail
    thumbnail.classList.add('active');

    // Change main image (swap thumbnail size for full size)
    mainImage.src = thumbnail.src.replace('w=100&h=100', 'w=600&h=600');
}

// =============================================================================
// COLOR SELECTION
// =============================================================================

/**
 * Setup click handlers for color selection buttons
 */
function setupColorSelection() {
    const colorButtons = document.querySelectorAll('.color-btn');

    colorButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons
            colorButtons.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            btn.classList.add('active');
            // Update selected color in product state
            product.selectedColor = btn.dataset.color;
        });
    });
}

// =============================================================================
// QUANTITY CONTROLS
// =============================================================================

/**
 * Increase the quantity by 1 (max: 10)
 */
function increaseQty() {
    const qtyInput = document.getElementById('quantity');
    let currentQty = parseInt(qtyInput.value);
    if (currentQty < 10) {
        qtyInput.value = currentQty + 1;
    }
}

/**
 * Decrease the quantity by 1 (min: 1)
 */
function decreaseQty() {
    const qtyInput = document.getElementById('quantity');
    let currentQty = parseInt(qtyInput.value);
    if (currentQty > 1) {
        qtyInput.value = currentQty - 1;
    }
}

// =============================================================================
// CART MANAGEMENT
// =============================================================================

/**
 * Add the current product to cart
 * Used for static product pages
 */
function addToCart() {
    const quantity = parseInt(document.getElementById('quantity').value);

    const cartItem = {
        id: Date.now(), // Unique ID using timestamp
        name: product.name,
        price: product.price,
        color: product.selectedColor,
        quantity: quantity,
        image: product.image,
        subtotal: product.price * quantity
    };

    cart.push(cartItem);
    saveCart();
    updateCartCount();
    updateCartDisplay();
    toggleCart();
    showNotification('Product added to cart!');
}

/**
 * Update the cart count badge in the header
 */
function updateCartCount() {
    const cartCount = document.getElementById('cartCount');
    if (cartCount) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
    }
}

/**
 * Render the cart items in the sidebar
 */
function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');

    // Exit if elements don't exist (e.g., on checkout page)
    if (!cartItems || !cartTotal) return;

    // Empty cart state
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
        cartTotal.textContent = '$0.00';
        return;
    }

    // Build cart items HTML
    let total = 0;
    let html = '';

    cart.forEach((item, index) => {
        total += item.subtotal;
        html += `
            <div class="cart-item">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image"
                     onerror="this.src='https://via.placeholder.com/80'">
                <div class="cart-item-details">
                    <div class="cart-item-title">${item.name}</div>
                    <div class="cart-item-color">${item.color || 'Standard'}</div>
                    <div class="cart-item-price">$${item.price.toFixed(2)} × ${item.quantity}</div>
                </div>
                <button class="cart-item-remove" onclick="removeFromCart(${index})" title="Remove item">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });

    cartItems.innerHTML = html;
    cartTotal.textContent = `$${total.toFixed(2)}`;
}

/**
 * Remove an item from the cart by index
 * @param {number} index - Index of item to remove
 */
function removeFromCart(index) {
    cart.splice(index, 1);
    saveCart();
    updateCartCount();
    updateCartDisplay();
    showNotification('Item removed from cart');
}

/**
 * Save cart to localStorage
 */
function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

/**
 * Clear all items from cart
 */
function clearCart() {
    cart = [];
    saveCart();
    updateCartCount();
    updateCartDisplay();
}

// =============================================================================
// CART SIDEBAR TOGGLE
// =============================================================================

// Setup cart icon click handler
const cartIcon = document.getElementById('cartIcon');
if (cartIcon) {
    cartIcon.addEventListener('click', toggleCart);
}

/**
 * Toggle the cart sidebar open/closed
 */
function toggleCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');

    if (cartSidebar && cartOverlay) {
        cartSidebar.classList.toggle('active');
        cartOverlay.classList.toggle('active');

        // Update display when opening
        if (cartSidebar.classList.contains('active')) {
            updateCartDisplay();
        }
    }
}

/**
 * Proceed to checkout page
 */
function proceedToCheckout() {
    if (cart.length === 0) {
        showNotification('Your cart is empty!');
        return;
    }
    window.location.href = 'checkout.php';
}

// =============================================================================
// NOTIFICATION SYSTEM
// =============================================================================

/**
 * Display a toast notification
 * @param {string} message - Message to display
 * @param {string} type - Type of notification ('success', 'error', 'info')
 */
function showNotification(message, type = 'success') {
    // Set background color based on type
    const bgColors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#3b82f6'
    };

    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${bgColors[type] || bgColors.success};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 2000;
        animation: slideIn 0.3s ease-out;
        display: flex;
        align-items: center;
        gap: 10px;
    `;
    notification.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;

    document.body.appendChild(notification);

    // Auto-remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// =============================================================================
// CSS ANIMATIONS (Injected dynamically)
// =============================================================================

const style = document.createElement('style');
style.textContent = `
    /* Slide in from right animation */
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    /* Slide out to right animation */
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
