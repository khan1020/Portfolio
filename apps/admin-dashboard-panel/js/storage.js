// Storage Manager with LocalStorage
const Storage = {
    get(key) {
        try {
            return JSON.parse(localStorage.getItem(key));
        } catch {
            return null;
        }
    },

    set(key, value) {
        localStorage.setItem(key, JSON.stringify(value));
    },

    remove(key) {
        localStorage.removeItem(key);
    },

    clear() {
        localStorage.clear();
    },

    // Initialize demo data
    initDemo() {
        if (!this.get('initialized')) {
            this.set('admin', { username: 'admin', password: 'admin123', name: 'Admin User' });
            this.set('users', [
                { id: 1, name: 'Afzal Khan', email: 'afzalkhanrind92@gmail.com', role: 'Admin', status: 'Active', joined: '2026-01-20' },
                { id: 2, name: 'Sarah Williams', email: 'sarah.w@company.com', role: 'Editor', status: 'Active', joined: '2026-02-20' },
                { id: 3, name: 'Mike Johnson', email: 'mike.j@company.com', role: 'Member', status: 'Active', joined: '2026-03-10' },
                { id: 4, name: 'Emma Wilson', email: 'emma@company.com', role: 'Editor', status: 'Inactive', joined: '2026-04-05' },
                { id: 5, name: 'Alex Rivera', email: 'alex.r@company.com', role: 'Member', status: 'Active', joined: '2026-05-12' }
            ]);
            this.set('products', [
                { id: 1, name: 'Laptop Pro', category: 'Electronics', price: 1299, stock: 45, status: 'Available' },
                { id: 2, name: 'Wireless Mouse', category: 'Electronics', price: 29, stock: 120, status: 'Available' },
                { id: 3, name: 'Office Chair', category: 'Furniture', price: 199, stock: 15, status: 'Low Stock' },
                { id: 4, name: 'Standing Desk', category: 'Furniture', price: 499, stock: 30, status: 'Available' },
                { id: 5, name: 'HD Webcam', category: 'Electronics', price: 79, stock: 8, status: 'Low Stock' }
            ]);
            this.set('orders', [
                { id: 1001, customer: 'Customer A', product: 'Laptop Pro', amount: 1299, status: 'Completed', date: '2026-06-01' },
                { id: 1002, customer: 'Customer B', product: 'Wireless Mouse', amount: 29, status: 'Pending', date: '2026-06-05' },
                { id: 1003, customer: 'Customer C', product: 'Office Chair', amount: 199, status: 'Shipped', date: '2026-06-10' },
                { id: 1004, customer: 'Customer D', product: 'Standing Desk', amount: 499, status: 'Processing', date: '2026-06-12' },
                { id: 1005, customer: 'Customer E', product: 'HD Webcam', amount: 79, status: 'Cancelled', date: '2026-06-15' }
            ]);
            this.set('initialized', true);
        }
    }
};

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `notification ${type} show`;
    setTimeout(() => notification.classList.remove('show'), 3000);
}

// Initialize on load
Storage.initDemo();
