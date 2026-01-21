<?php
/**
 * Admin Dashboard - Analytics & Management
 * @author Afzal Khan
 */
$conn = new mysqli("localhost", "root", "");
$conn->query("CREATE DATABASE IF NOT EXISTS admin_dashboard_db");
$conn->select_db("admin_dashboard_db");

$conn->query("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100), email VARCHAR(100), role VARCHAR(50), status VARCHAR(20), created_at DATE)");
$conn->query("CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY, customer VARCHAR(100), amount DECIMAL(10,2), status VARCHAR(20), created_at DATE)");
$conn->query("CREATE TABLE IF NOT EXISTS products (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100), category VARCHAR(50), stock INT, price DECIMAL(10,2))");

// Sample data
if ($conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'] == 0) {
    for ($i = 1; $i <= 15; $i++) {
        $name = "User $i"; $email = "user$i@example.com";
        $role = ['Admin', 'Editor', 'Member'][array_rand(['Admin', 'Editor', 'Member'])];
        $status = rand(0,10) > 2 ? 'active' : 'inactive';
        $date = date('Y-m-d', strtotime("-" . rand(1, 60) . " days"));
        $conn->query("INSERT INTO users (name, email, role, status, created_at) VALUES ('$name', '$email', '$role', '$status', '$date')");
    }
    for ($i = 1; $i <= 25; $i++) {
        $customer = "Customer $i"; $amount = rand(50, 500);
        $status = ['pending', 'completed', 'shipped', 'cancelled'][array_rand(['pending', 'completed', 'shipped', 'cancelled'])];
        $date = date('Y-m-d', strtotime("-" . rand(0, 30) . " days"));
        $conn->query("INSERT INTO orders (customer, amount, status, created_at) VALUES ('$customer', $amount, '$status', '$date')");
    }
    $products = [['iPhone 15', 'Electronics', 45, 999], ['MacBook Pro', 'Electronics', 23, 1999], ['Nike Shoes', 'Fashion', 120, 149], ['Desk Lamp', 'Home', 89, 39], ['Headphones', 'Electronics', 67, 199]];
    foreach ($products as $p) $conn->query("INSERT INTO products (name, category, stock, price) VALUES ('{$p[0]}', '{$p[1]}', {$p[2]}, {$p[3]})");
}

// Stats
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalOrders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$totalRevenue = $conn->query("SELECT SUM(amount) as s FROM orders WHERE status = 'completed'")->fetch_assoc()['s'] ?? 0;
$activeUsers = $conn->query("SELECT COUNT(*) as c FROM users WHERE status = 'active'")->fetch_assoc()['c'];

// Chart data
$chartData = $conn->query("SELECT DATE(created_at) as date, SUM(amount) as total FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date");
$chartLabels = []; $chartValues = [];
while ($r = $chartData->fetch_assoc()) { $chartLabels[] = date('M j', strtotime($r['date'])); $chartValues[] = $r['total']; }

$recentOrders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
$users = $conn->query("SELECT * FROM users LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #0f172a; color: white; padding: 25px; position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar .logo { font-size: 1.5rem; font-weight: 700; margin-bottom: 40px; display: flex; align-items: center; gap: 12px; }
        .sidebar nav a { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #94a3b8; text-decoration: none; border-radius: 10px; margin-bottom: 5px; font-weight: 500; transition: all 0.2s; }
        .sidebar nav a:hover, .sidebar nav a.active { background: #1e293b; color: white; }
        .sidebar nav a i { width: 20px; text-align: center; }
        .main { flex: 1; margin-left: 260px; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-size: 1.75rem; color: #0f172a; }
        .header-actions { display: flex; gap: 15px; align-items: center; }
        .header-actions input { padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; width: 250px; }
        .btn { padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .stat-card .icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 15px; }
        .stat-card .icon.blue { background: #dbeafe; color: #3b82f6; }
        .stat-card .icon.green { background: #d1fae5; color: #10b981; }
        .stat-card .icon.purple { background: #ede9fe; color: #8b5cf6; }
        .stat-card .icon.orange { background: #ffedd5; color: #f97316; }
        .stat-card h3 { font-size: 2rem; margin-bottom: 5px; color: #0f172a; }
        .stat-card p { color: #64748b; font-size: 0.9rem; }
        .grid-2 { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; }
        .card { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .card h2 { font-size: 1.1rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { color: #64748b; font-weight: 600; font-size: 0.85rem; }
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge.completed { background: #d1fae5; color: #065f46; }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .badge.shipped { background: #dbeafe; color: #1d4ed8; }
        .badge.cancelled { background: #fee2e2; color: #991b1b; }
        .badge.active { background: #d1fae5; color: #065f46; }
        .badge.inactive { background: #f3f4f6; color: #6b7280; }
        .user-row { display: flex; align-items: center; gap: 12px; }
        .user-row img { width: 35px; height: 35px; border-radius: 50%; }
        @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .grid-2 { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .sidebar { display: none; } .main { margin-left: 0; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo"><i class="fas fa-cube"></i> AdminPanel</div>
        <nav>
            <a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="#"><i class="fas fa-users"></i> Users</a>
            <a href="#"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="#"><i class="fas fa-box"></i> Products</a>
            <a href="#"><i class="fas fa-chart-bar"></i> Analytics</a>
            <a href="#"><i class="fas fa-cog"></i> Settings</a>
            <a href="../../index.html"><i class="fas fa-arrow-left"></i> Portfolio</a>
        </nav>
    </aside>

    <main class="main">
        <div class="header">
            <h1>Dashboard Overview</h1>
            <div class="header-actions">
                <input type="text" placeholder="Search...">
                <button class="btn"><i class="fas fa-plus"></i> Add New</button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon blue"><i class="fas fa-users"></i></div>
                <h3><?php echo number_format($totalUsers); ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <div class="icon green"><i class="fas fa-shopping-cart"></i></div>
                <h3><?php echo number_format($totalOrders); ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <div class="icon purple"><i class="fas fa-dollar-sign"></i></div>
                <h3>$<?php echo number_format($totalRevenue); ?></h3>
                <p>Revenue</p>
            </div>
            <div class="stat-card">
                <div class="icon orange"><i class="fas fa-user-check"></i></div>
                <h3><?php echo number_format($activeUsers); ?></h3>
                <p>Active Users</p>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <h2><i class="fas fa-chart-line"></i> Revenue (Last 7 Days)</h2>
                <canvas id="revenueChart" height="120"></canvas>
            </div>
            <div class="card">
                <h2><i class="fas fa-clock"></i> Recent Orders</h2>
                <table>
                    <thead><tr><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php while ($o = $recentOrders->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($o['customer']); ?></td>
                            <td>$<?php echo number_format($o['amount'], 2); ?></td>
                            <td><span class="badge <?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2><i class="fas fa-users"></i> Users</h2>
            <table>
                <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead>
                <tbody>
                    <?php while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="user-row">
                                <img src="https://i.pravatar.cc/35?u=<?php echo $u['id']; ?>" alt="">
                                <?php echo htmlspecialchars($u['name']); ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo $u['role']; ?></td>
                        <td><span class="badge <?php echo $u['status']; ?>"><?php echo ucfirst($u['status']); ?></span></td>
                        <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($chartValues); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
