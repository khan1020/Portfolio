<?php
/**
 * User Dashboard (Protected Page)
 * @author Afzal Khan
 */
require_once 'includes/db.php';
requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SecureAuth</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-page">
    <header class="dashboard-header">
        <div class="container">
            <div class="header-content">
                <div class="logo"><i class="fas fa-shield-alt"></i> SecureAuth</div>
                <div class="user-menu">
                    <span class="user-name"><i class="fas fa-user-circle"></i> <?php echo e($user['username']); ?></span>
                    <a href="logout.php" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="dashboard-main">
        <div class="container">
            <div class="welcome-card">
                <div class="welcome-icon"><i class="fas fa-check-circle"></i></div>
                <h1>Welcome, <?php echo e($user['full_name'] ?: $user['username']); ?>!</h1>
                <p>You have successfully authenticated.</p>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h3><i class="fas fa-user"></i> Account Information</h3>
                    <table class="info-table">
                        <tr><td>Username</td><td><?php echo e($user['username']); ?></td></tr>
                        <tr><td>Email</td><td><?php echo e($user['email']); ?></td></tr>
                        <tr><td>Full Name</td><td><?php echo e($user['full_name'] ?: 'Not set'); ?></td></tr>
                        <tr><td>Member Since</td><td><?php echo date('F j, Y', strtotime($user['created_at'])); ?></td></tr>
                        <tr><td>Status</td><td><span class="badge badge-success">Active</span></td></tr>
                    </table>
                </div>

                <div class="info-card">
                    <h3><i class="fas fa-lock"></i> Security Features</h3>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Password hashing with bcrypt</li>
                        <li><i class="fas fa-check"></i> Session-based authentication</li>
                        <li><i class="fas fa-check"></i> SQL injection protection</li>
                        <li><i class="fas fa-check"></i> XSS prevention</li>
                        <li><i class="fas fa-check"></i> Password reset tokens</li>
                    </ul>
                </div>
            </div>

            <div class="action-buttons">
                <a href="../../index.html" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
                <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </main>
</body>
</html>
