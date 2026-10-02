// Authentication System
const Auth = {
    isLoggedIn() {
        return Storage.get('loggedIn') === true;
    },

    login(username, password) {
        const admin = Storage.get('admin');
        if (admin && admin.username === username && admin.password === password) {
            Storage.set('loggedIn', true);
            Storage.set('currentUser', admin.name);
            return true;
        }
        return false;
    },

    logout() {
        Storage.remove('loggedIn');
        Storage.remove('currentUser');
        window.location.href = 'login.html';
    },

    checkAuth() {
        if (!this.isLoggedIn()) window.location.href = 'login.html';
    }
};

// Login Page
if (document.querySelector('.login-page')) {
    if (Auth.isLoggedIn()) window.location.href = 'index.html';

    document.getElementById('loginForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        if (Auth.login(username, password)) {
            showNotification('Login successful!', 'success');
            setTimeout(() => window.location.href = 'index.html', 1000);
        } else {
            showNotification('Invalid credentials', 'error');
        }
    });

    document.getElementById('loadDemoBtn').addEventListener('click', () => {
        Storage.initDemo();
        showNotification('Demo data loaded! Use admin/admin123', 'success');
    });
}

// Dashboard Page
if (!document.querySelector('.login-page')) {
    Auth.checkAuth();
    document.getElementById('userName').textContent = Storage.get('currentUser') || 'Admin';
    document.getElementById('logoutBtn').addEventListener('click', () => {
        if (confirm('Logout?')) Auth.logout();
    });
}
