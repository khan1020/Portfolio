<?php
/**
 * OTP Verification System
 * @author Afzal Khan
 */
session_start();

// Generate OTP
if (!isset($_SESSION['otp'])) {
    $_SESSION['otp'] = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['otp_expires'] = time() + 300; // 5 minutes
    $_SESSION['otp_attempts'] = 0;
}

$step = isset($_GET['step']) ? $_GET['step'] : 'phone';
$error = '';
$success = false;

// Handle phone submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_otp'])) {
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
    if (strlen($phone) >= 10) {
        $_SESSION['phone'] = $_POST['phone'];
        $_SESSION['otp'] = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['otp_expires'] = time() + 300;
        $_SESSION['otp_attempts'] = 0;
        header("Location: index.php?step=verify");
        exit;
    } else {
        $error = 'Please enter a valid phone number';
    }
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $entered = implode('', $_POST['otp']);
    $_SESSION['otp_attempts']++;
    
    if (time() > $_SESSION['otp_expires']) {
        $error = 'OTP has expired. Please request a new one.';
    } elseif ($_SESSION['otp_attempts'] > 5) {
        $error = 'Too many attempts. Please request a new OTP.';
    } elseif ($entered === $_SESSION['otp']) {
        $success = true;
        unset($_SESSION['otp']);
    } else {
        $error = 'Invalid OTP. Please try again.';
    }
}

// Resend OTP
if (isset($_GET['resend'])) {
    $_SESSION['otp'] = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['otp_expires'] = time() + 300;
    $_SESSION['otp_attempts'] = 0;
    header("Location: index.php?step=verify&resent=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { width: 100%; max-width: 420px; }
        .card { background: white; border-radius: 24px; padding: 50px 40px; box-shadow: 0 25px 50px rgba(0,0,0,0.3); text-align: center; }
        .icon-wrapper { width: 80px; height: 80px; background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; }
        .icon-wrapper i { font-size: 2rem; color: white; }
        h1 { font-size: 1.5rem; color: #0f172a; margin-bottom: 10px; }
        p { color: #64748b; margin-bottom: 30px; }
        .phone-display { background: #f1f5f9; padding: 12px 20px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; color: #0f172a; }
        .form-group { margin-bottom: 25px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; }
        .phone-input { display: flex; gap: 10px; }
        .phone-input select { padding: 15px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1rem; background: white; }
        .phone-input input { flex: 1; padding: 15px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1rem; }
        .phone-input input:focus { outline: none; border-color: #3b82f6; }
        .otp-inputs { display: flex; gap: 10px; justify-content: center; margin: 30px 0; }
        .otp-inputs input { width: 55px; height: 65px; text-align: center; font-size: 1.75rem; font-weight: 700; border: 2px solid #e5e7eb; border-radius: 12px; transition: all 0.2s; }
        .otp-inputs input:focus { outline: none; border-color: #3b82f6; background: #f0f9ff; }
        .btn { width: 100%; padding: 16px; background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); color: white; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: transform 0.2s; }
        .btn:hover { transform: scale(1.02); }
        .resend { margin-top: 25px; color: #64748b; }
        .resend a { color: #3b82f6; text-decoration: none; font-weight: 600; }
        .timer { color: #ef4444; font-weight: 600; }
        .alert { padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-info { background: #dbeafe; color: #1d4ed8; }
        .success-card .icon-wrapper { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .demo-otp { background: #fef3c7; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .demo-otp code { font-size: 1.5rem; font-weight: 700; color: #92400e; letter-spacing: 5px; }
        .back-link { display: block; text-align: center; margin-top: 25px; color: rgba(255,255,255,0.6); text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="card success-card">
                <div class="icon-wrapper"><i class="fas fa-check"></i></div>
                <h1>Verified Successfully!</h1>
                <p>Your phone number has been verified. You can now proceed.</p>
                <a href="index.php?step=phone" class="btn"><i class="fas fa-redo"></i> Try Again</a>
            </div>
        <?php elseif ($step === 'verify'): ?>
            <div class="card">
                <div class="icon-wrapper"><i class="fas fa-shield-alt"></i></div>
                <h1>Enter OTP</h1>
                <p>We sent a verification code to<br><?php echo htmlspecialchars($_SESSION['phone'] ?? '+92 300 *******'); ?></p>
                
                <?php if (isset($_GET['resent'])): ?>
                    <div class="alert alert-success"><i class="fas fa-check"></i> New OTP sent!</div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="demo-otp">
                    <small>Demo OTP (normally sent via SMS):</small><br>
                    <code><?php echo $_SESSION['otp']; ?></code>
                </div>
                
                <form method="POST" id="otpForm">
                    <div class="otp-inputs">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                            <input type="text" name="otp[]" maxlength="1" pattern="[0-9]" inputmode="numeric" required 
                                   onfocus="this.select()" 
                                   oninput="if(this.value.length===1)this.nextElementSibling?.focus()">
                        <?php endfor; ?>
                    </div>
                    <button type="submit" name="verify_otp" class="btn"><i class="fas fa-check-circle"></i> Verify OTP</button>
                </form>
                
                <div class="resend">
                    Didn't receive code? <a href="index.php?resend=1">Resend OTP</a>
                    <br><br>
                    <span class="timer" id="timer">Expires in: <span id="countdown">5:00</span></span>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="icon-wrapper"><i class="fas fa-mobile-alt"></i></div>
                <h1>Phone Verification</h1>
                <p>Enter your phone number to receive a one-time password</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <div class="phone-input">
                            <select name="country">
                                <option value="+92">🇵🇰 +92</option>
                                <option value="+1">🇺🇸 +1</option>
                                <option value="+44">🇬🇧 +44</option>
                                <option value="+91">🇮🇳 +91</option>
                            </select>
                            <input type="tel" name="phone" placeholder="300 1234567" required>
                        </div>
                    </div>
                    <button type="submit" name="send_otp" class="btn"><i class="fas fa-paper-plane"></i> Send OTP</button>
                </form>
            </div>
        <?php endif; ?>
        
        <a href="../../index.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
    </div>

    <?php if ($step === 'verify'): ?>
    <script>
        // Countdown timer
        let remaining = <?php echo max(0, $_SESSION['otp_expires'] - time()); ?>;
        const countdown = document.getElementById('countdown');
        const interval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(interval);
                countdown.textContent = 'Expired';
                countdown.style.color = '#ef4444';
            } else {
                const min = Math.floor(remaining / 60);
                const sec = remaining % 60;
                countdown.textContent = `${min}:${sec.toString().padStart(2, '0')}`;
            }
        }, 1000);

        // Auto-submit when all digits entered
        const inputs = document.querySelectorAll('.otp-inputs input');
        inputs.forEach((input, i) => {
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const text = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                text.split('').forEach((char, j) => { if (inputs[j]) inputs[j].value = char; });
                inputs[Math.min(text.length, 5)].focus();
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && i > 0) inputs[i-1].focus();
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
