<?php
/**
 * User Registration
 * @author Afzal Khan
 */
require_once 'includes/db.php';
requireGuest();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $fullName = trim($_POST['full_name']);
    
    // Validation
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if ($password !== $confirm) $errors[] = 'Passwords do not match';
    
    // Check existing
    $check = $conn->query("SELECT id FROM users WHERE username = '" . $conn->real_escape_string($username) . "' OR email = '" . $conn->real_escape_string($email) . "'");
    if ($check->num_rows > 0) $errors[] = 'Username or email already exists';
    
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hash, $fullName);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = 'Registration failed. Try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | SecureAuth</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-shield-alt auth-icon"></i>
                <h1>Create Account</h1>
                <p>Join SecureAuth today</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Registration successful! <a href="login.php">Login now</a>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $e): ?><p><i class="fas fa-exclamation-circle"></i> <?php echo e($e); ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="full_name" placeholder="John Doe" value="<?php echo isset($fullName) ? e($fullName) : ''; ?>">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-at"></i> Username *</label>
                    <input type="text" name="username" required placeholder="johndoe" value="<?php echo isset($username) ? e($username) : ''; ?>">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" name="email" required placeholder="john@example.com" value="<?php echo isset($email) ? e($email) : ''; ?>">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password *</label>
                    <input type="password" name="password" required placeholder="Min 6 characters">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirm Password *</label>
                    <input type="password" name="confirm_password" required placeholder="Repeat password">
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
        <a href="../../index.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
    </div>
</body>
</html>
