const Pages = {
    chartInstances: {}, // Store chart instances to destroy them later

    dashboard() {
        const users = Storage.get('users') || [];
        const products = Storage.get('products') || [];
        const orders = Storage.get('orders') || [];
        const revenue = orders.reduce((sum, o) => sum + o.amount, 0);
        const activeUsers = users.filter(u => u.status === 'Active').length;

        // Initialize charts after DOM is ready
        setTimeout(() => {
            // Destroy existing charts to prevent memory leaks
            if (this.chartInstances.revenue) {
                this.chartInstances.revenue.destroy();
            }
            if (this.chartInstances.category) {
                this.chartInstances.category.destroy();
            }

            const revenueCanvas = document.getElementById('revenueChart');
            const categoryCanvas = document.getElementById('categoryChart');

            if (revenueCanvas) {
                this.chartInstances.revenue = new Chart(revenueCanvas, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{ label: 'Revenue', data: [1200, 1900, 1500, 2100, 1800, 2400, 2800], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            }

            if (categoryCanvas) {
                this.chartInstances.category = new Chart(categoryCanvas, {
                    type: 'doughnut',
                    data: { labels: ['Electronics', 'Furniture', 'Clothing', 'Books'], datasets: [{ data: [45, 25, 20, 10], backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'] }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        }, 100);

        return `
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon blue"><i class="fas fa-users"></i></div>
                    <h3>${users.length}</h3>
                    <p>Total Users</p>
                </div>
                <div class="stat-card">
                    <div class="icon green"><i class="fas fa-shopping-cart"></i></div>
                    <h3>${orders.length}</h3>
                    <p>Total Orders</p>
                </div>
                <div class="stat-card">
                    <div class="icon purple"><i class="fas fa-dollar-sign"></i></div>
                    <h3>$${revenue.toLocaleString()}</h3>
                    <p>Revenue</p>
                </div>
                <div class="stat-card">
                    <div class="icon orange"><i class="fas fa-user-check"></i></div>
                    <h3>${activeUsers}</h3>
                    <p>Active Users</p>
                </div>
            </div>
            <div class="grid-2">
                <div class="card">
                    <h2><i class="fas fa-chart-line"></i> Revenue (Last 7 Days)</h2>
                    <div style="height:250px;position:relative;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                <div class="card">
                    <h2><i class="fas fa-chart-pie"></i> Sales by Category</h2>
                    <div style="height:250px;position:relative;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="card">
                <h2><i class="fas fa-shopping-cart"></i> Recent Orders</h2>
                <table>
                    <thead><tr><th>Customer</th><th>Product</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>${orders.slice(0, 5).map(o => `<tr><td>${o.customer}</td><td>${o.product}</td><td>$${o.amount}</td><td><span class="badge ${o.status.toLowerCase()}">${o.status}</span></td></tr>`).join('')}</tbody>
                </table>
            </div>
        `;
    },

    users() {
        const users = Storage.get('users') || [];
        return `
            <div class="table-card">
                <div class="table-header">
                    <h2>Users Management</h2>
                    <div class="table-tools">
                        <input type="text" id="searchUsers" placeholder="Search users..." class="search-input">
                        <button class="btn btn-primary" onclick="CRUD.showModal('user')"><i class="fas fa-plus"></i> Add User</button>
                    </div>
                </div>
                <table id="usersTable">
                    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                    <tbody>${users.map(u => `
                        <tr>
                            <td>#${u.id}</td>
                            <td><div class="user-row"><div class="user-avatar">${u.name[0]}</div>${u.name}</div></td>
                            <td>${u.email}</td>
                            <td>${u.role}</td>
                            <td><span class="badge ${u.status.toLowerCase()}">${u.status}</span></td>
                            <td>${u.joined}</td>
                            <td class="action-buttons">
                                <button class="icon-btn edit" onclick="CRUD.edit('user',${u.id})"><i class="fas fa-edit"></i></button>
                                <button class="icon-btn delete" onclick="CRUD.delete('user',${u.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `).join('')}</tbody>
                </table>
            </div>
        `;
    },

    products() {
        const products = Storage.get('products') || [];
        return `
            <div class="table-card">
                <div class="table-header">
                    <h2>Products Management</h2>
                    <div class="table-tools">
                        <input type="text" id="searchProducts" placeholder="Search products..." class="search-input">
                        <button class="btn btn-primary" onclick="CRUD.showModal('product')"><i class="fas fa-plus"></i> Add Product</button>
                    </div>
                </div>
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>${products.map(p => `
                        <tr>
                            <td>#${p.id}</td>
                            <td>${p.name}</td>
                            <td>${p.category}</td>
                            <td>$${p.price}</td>
                            <td>${p.stock}</td>
                            <td><span class="badge ${p.stock < 20 ? 'low' : 'active'}">${p.status}</span></td>
                            <td class="action-buttons">
                                <button class="icon-btn edit" onclick="CRUD.edit('product',${p.id})"><i class="fas fa-edit"></i></button>
                                <button class="icon-btn delete" onclick="CRUD.delete('product',${p.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `).join('')}</tbody>
                </table>
            </div>
        `;
    },

    orders() {
        const orders = Storage.get('orders') || [];
        return `
            <div class="table-card">
                <div class="table-header">
                    <h2>Orders Management</h2>
                    <div class="table-tools">
                        <input type="text" id="searchOrders" placeholder="Search orders..." class="search-input">
                        <button class="btn btn-primary" onclick="CRUD.showModal('order')"><i class="fas fa-plus"></i> Add Order</button>
                    </div>
                </div>
                <table>
                    <thead><tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Amount</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>${orders.map(o => `
                        <tr>
                            <td>#${o.id}</td>
                            <td>${o.customer}</td>
                            <td>${o.product}</td>
                            <td>$${o.amount}</td>
                            <td><span class="badge ${o.status.toLowerCase()}">${o.status}</span></td>
                            <td>${o.date}</td>
                            <td class="action-buttons">
                                <button class="icon-btn edit" onclick="CRUD.edit('order',${o.id})"><i class="fas fa-edit"></i></button>
                                <button class="icon-btn delete" onclick="CRUD.delete('order',${o.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `).join('')}</tbody>
                </table>
            </div>
        `;
    },

    analytics() {
        // Don't call dashboard() - create a simple placeholder instead
        return `
            <div class="card">
                <h2><i class="fas fa-chart-bar"></i> Analytics Overview</h2>
                <p style="padding:60px 40px;text-align:center;color:var(--text-secondary);">
                    <i class="fas fa-chart-line" style="font-size:64px;margin-bottom:24px;display:block;opacity:0.3;"></i>
                    <strong style="font-size:1.2rem;display:block;margin-bottom:12px;">Advanced Analytics Coming Soon</strong>
                    For now, please use the main <strong>Dashboard</strong> page to view KPIs, charts, and statistics.
                </p>
            </div>
        `;
    },

    settings() {
        return `
            <div class="card" style="max-width:800px">
                <h2><i class="fas fa-cog"></i> Settings</h2>
                <div class="settings-section">
                    <h3>Data Management</h3>
                    <p>Export all data or clear and reload demo data</p>
                    <div style="display:flex;gap:12px;margin-top:16px">
                        <button class="btn btn-primary" onclick="exportData()"><i class="fas fa-download"></i> Export Data</button>
                        <button class="btn btn-danger" onclick="clearData()"><i class="fas fa-trash"></i> Clear & Reload Demo</button>
                    </div>
                </div>
            </div>
        `;
    }
};

function exportData() {
    const data = { users: Storage.get('users'), products: Storage.get('products'), orders: Storage.get('orders') };
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'dashboard-data.json';
    a.click();
    showNotification('Data exported!', 'success');
}

function clearData() {
    if (confirm('Clear all data and reload demo?')) {
        Storage.clear();
        Storage.initDemo();
        showNotification('Data cleared and reloaded!', 'success');
        setTimeout(() => location.reload(), 1500);
    }
}
