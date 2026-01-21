<?php
/**
 * Expense Tracker with Charts
 * @author Afzal Khan
 */
$conn = new mysqli("localhost", "root", "");
$conn->query("CREATE DATABASE IF NOT EXISTS expense_tracker_db");
$conn->select_db("expense_tracker_db");

$conn->query("CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('income', 'expense') NOT NULL,
    category VARCHAR(50),
    description VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Sample data
$check = $conn->query("SELECT COUNT(*) as c FROM transactions")->fetch_assoc();
if ($check['c'] == 0) {
    $samples = [
        ['income', 'Salary', 'Monthly salary', 5000, date('Y-m-01')],
        ['expense', 'Food', 'Grocery shopping', 150, date('Y-m-03')],
        ['expense', 'Transport', 'Fuel', 80, date('Y-m-05')],
        ['expense', 'Entertainment', 'Netflix subscription', 15, date('Y-m-05')],
        ['income', 'Freelance', 'Website project', 800, date('Y-m-10')],
        ['expense', 'Bills', 'Electricity bill', 120, date('Y-m-12')],
        ['expense', 'Food', 'Restaurant dinner', 45, date('Y-m-15')],
        ['expense', 'Shopping', 'New headphones', 99, date('Y-m-18')],
    ];
    foreach ($samples as $s) {
        $conn->query("INSERT INTO transactions (type, category, description, amount, date) VALUES ('{$s[0]}', '{$s[1]}', '{$s[2]}', {$s[3]}, '{$s[4]}')");
    }
}

// Handle form
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $type = $_POST['type'];
        $cat = $conn->real_escape_string($_POST['category']);
        $desc = $conn->real_escape_string($_POST['description']);
        $amount = floatval($_POST['amount']);
        $date = $_POST['date'];
        $conn->query("INSERT INTO transactions (type, category, description, amount, date) VALUES ('$type', '$cat', '$desc', $amount, '$date')");
        $msg = 'Transaction added!';
    }
}
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM transactions WHERE id = " . (int)$_GET['delete']);
    $msg = 'Transaction deleted!';
}

// Get stats
$totals = $conn->query("SELECT 
    COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE 0 END), 0) as income,
    COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END), 0) as expense
    FROM transactions WHERE MONTH(date) = MONTH(CURDATE())")->fetch_assoc();

$categoryData = $conn->query("SELECT category, SUM(amount) as total FROM transactions WHERE type='expense' AND MONTH(date) = MONTH(CURDATE()) GROUP BY category ORDER BY total DESC LIMIT 6");
$categories = []; while ($r = $categoryData->fetch_assoc()) $categories[] = $r;

$transactions = $conn->query("SELECT * FROM transactions ORDER BY date DESC, id DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpenseTrack - Personal Finance</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0fdf4; min-height: 100vh; }
        .header { background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; padding: 20px; }
        .header .container { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; display: flex; align-items: center; gap: 10px; }
        .header a { color: white; text-decoration: none; opacity: 0.8; }
        .container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-label { color: #6b7280; font-size: 0.9rem; margin-bottom: 5px; }
        .stat-value { font-size: 2rem; font-weight: 700; }
        .stat-card.income .stat-value { color: #10b981; }
        .stat-card.expense .stat-value { color: #ef4444; }
        .stat-card.balance .stat-value { color: #3b82f6; }
        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 30px; }
        .card { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h3 { margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 0.9rem; color: #374151; }
        input, select { width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem; }
        input:focus, select:focus { outline: none; border-color: #10b981; }
        .type-toggle { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
        .type-btn { padding: 10px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; text-align: center; font-weight: 600; transition: all 0.2s; }
        .type-btn.income { color: #10b981; }
        .type-btn.expense { color: #ef4444; }
        .type-btn.active { background: currentColor; color: white; border-color: currentColor; }
        .btn { width: 100%; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem; }
        .transaction-list { max-height: 400px; overflow-y: auto; }
        .transaction { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #f3f4f6; }
        .transaction:hover { background: #f9fafb; }
        .trans-info { display: flex; align-items: center; gap: 15px; }
        .trans-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
        .trans-icon.income { background: #d1fae5; color: #10b981; }
        .trans-icon.expense { background: #fee2e2; color: #ef4444; }
        .trans-details h4 { font-size: 0.95rem; }
        .trans-details p { font-size: 0.8rem; color: #6b7280; }
        .trans-amount { font-weight: 700; }
        .trans-amount.income { color: #10b981; }
        .trans-amount.expense { color: #ef4444; }
        .delete-btn { color: #9ca3af; margin-left: 15px; cursor: pointer; }
        .delete-btn:hover { color: #ef4444; }
        .alert { padding: 12px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 15px; }
        @media (max-width: 900px) { .stats-grid, .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header class="header">
        <div class="container" style="padding: 0;">
            <h1><i class="fas fa-wallet"></i> ExpenseTrack</h1>
            <a href="../../index.html"><i class="fas fa-arrow-left"></i> Portfolio</a>
        </div>
    </header>

    <div class="container">
        <?php if ($msg): ?><div class="alert"><i class="fas fa-check"></i> <?php echo $msg; ?></div><?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card income">
                <div class="stat-label">Income (This Month)</div>
                <div class="stat-value">$<?php echo number_format($totals['income'], 2); ?></div>
            </div>
            <div class="stat-card expense">
                <div class="stat-label">Expenses (This Month)</div>
                <div class="stat-value">$<?php echo number_format($totals['expense'], 2); ?></div>
            </div>
            <div class="stat-card balance">
                <div class="stat-label">Balance</div>
                <div class="stat-value">$<?php echo number_format($totals['income'] - $totals['expense'], 2); ?></div>
            </div>
        </div>

        <div class="grid">
            <div>
                <div class="card">
                    <h3><i class="fas fa-plus-circle"></i> Add Transaction</h3>
                    <form method="POST">
                        <div class="type-toggle">
                            <label class="type-btn income active" onclick="setType('income')">
                                <input type="radio" name="type" value="income" checked hidden> <i class="fas fa-arrow-down"></i> Income
                            </label>
                            <label class="type-btn expense" onclick="setType('expense')">
                                <input type="radio" name="type" value="expense" hidden> <i class="fas fa-arrow-up"></i> Expense
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Amount *</label>
                            <input type="number" name="amount" step="0.01" required placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category">
                                <option>Food</option><option>Transport</option><option>Bills</option>
                                <option>Shopping</option><option>Entertainment</option><option>Health</option>
                                <option>Salary</option><option>Freelance</option><option>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="description" placeholder="What was this for?">
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <button type="submit" name="add" class="btn">Add Transaction</button>
                    </form>
                </div>

                <div class="card">
                    <h3><i class="fas fa-chart-pie"></i> Spending by Category</h3>
                    <canvas id="categoryChart" height="200"></canvas>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-list"></i> Recent Transactions</h3>
                <div class="transaction-list">
                    <?php while ($t = $transactions->fetch_assoc()): ?>
                        <div class="transaction">
                            <div class="trans-info">
                                <div class="trans-icon <?php echo $t['type']; ?>">
                                    <i class="fas fa-<?php echo $t['type'] === 'income' ? 'arrow-down' : 'arrow-up'; ?>"></i>
                                </div>
                                <div class="trans-details">
                                    <h4><?php echo htmlspecialchars($t['description'] ?: $t['category']); ?></h4>
                                    <p><?php echo $t['category']; ?> • <?php echo date('M j', strtotime($t['date'])); ?></p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span class="trans-amount <?php echo $t['type']; ?>">
                                    <?php echo $t['type'] === 'income' ? '+' : '-'; ?>$<?php echo number_format($t['amount'], 2); ?>
                                </span>
                                <a href="?delete=<?php echo $t['id']; ?>" class="delete-btn" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setType(type) {
            document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
            event.target.closest('.type-btn').classList.add('active');
            document.querySelector(`input[value="${type}"]`).checked = true;
        }

        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($categories, 'category')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map('floatval', array_column($categories, 'total'))); ?>,
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899']
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    </script>
</body>
</html>
