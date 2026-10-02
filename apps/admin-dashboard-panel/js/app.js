// App Initialization
const App = {
    init() {
        // Navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.dataset.page;
                if (page) {
                    this.loadPage(page);
                    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                }
            });
        });

        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
        document.getElementById('mobileMenuBtn')?.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
        });

        // Theme toggle
        const theme = Storage.get('theme') || 'light';
        document.documentElement.setAttribute('data-theme', theme);
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const newTheme = current === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            Storage.set('theme', newTheme);
        });

        // Load dashboard
        this.loadPage('dashboard');
    },

    loadPage(page) {
        const container = document.getElementById('pageContainer');
        const pageTitle = document.getElementById('pageTitle');
        const titles = { dashboard: 'Dashboard Overview', users: 'Users Management', products: 'Products Management', orders: 'Orders Management', analytics: 'Analytics', settings: 'Settings' };

        pageTitle.textContent = titles[page] || 'Dashboard';

        if (Pages[page]) {
            container.innerHTML = Pages[page]();
        } else {
            container.innerHTML = Pages.dashboard();
        }
    }
};

// Initialize when DOM loaded
if (!document.querySelector('.login-page')) {
    document.addEventListener('DOMContentLoaded', () => App.init());
}
