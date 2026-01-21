<?php
/**
 * URL Shortener with Analytics
 * @author Afzal Khan
 */
$conn = new mysqli("localhost", "root", "");
$conn->query("CREATE DATABASE IF NOT EXISTS url_shortener_db");
$conn->select_db("url_shortener_db");

$conn->query("CREATE TABLE IF NOT EXISTS urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_url TEXT NOT NULL,
    short_code VARCHAR(10) UNIQUE NOT NULL,
    clicks INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer VARCHAR(255),
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (url_id) REFERENCES urls(id)
)");

function generateCode($length = 6) {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}

$baseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['REQUEST_URI']);
$message = '';
$newUrl = null;

// Handle URL shortening
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shorten'])) {
    $url = trim($_POST['url']);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        // Check if already exists
        $existing = $conn->query("SELECT short_code FROM urls WHERE original_url = '" . $conn->real_escape_string($url) . "'")->fetch_assoc();
        if ($existing) {
            $newUrl = $baseUrl . '/r/' . $existing['short_code'];
        } else {
            $code = generateCode();
            while ($conn->query("SELECT id FROM urls WHERE short_code = '$code'")->num_rows > 0) {
                $code = generateCode();
            }
            $conn->query("INSERT INTO urls (original_url, short_code) VALUES ('" . $conn->real_escape_string($url) . "', '$code')");
            $newUrl = $baseUrl . '/r/' . $code;
        }
        $message = 'success';
    } else {
        $message = 'invalid';
    }
}

// Handle redirect
if (isset($_GET['r'])) {
    $code = $conn->real_escape_string($_GET['r']);
    $url = $conn->query("SELECT * FROM urls WHERE short_code = '$code'")->fetch_assoc();
    if ($url) {
        $conn->query("UPDATE urls SET clicks = clicks + 1 WHERE id = " . $url['id']);
        $ip = $_SERVER['REMOTE_ADDR'];
        $ua = $conn->real_escape_string($_SERVER['HTTP_USER_AGENT']);
        $ref = isset($_SERVER['HTTP_REFERER']) ? $conn->real_escape_string($_SERVER['HTTP_REFERER']) : '';
        $conn->query("INSERT INTO clicks (url_id, ip_address, user_agent, referrer) VALUES ({$url['id']}, '$ip', '$ua', '$ref')");
        header("Location: " . $url['original_url']);
        exit;
    }
}

// Get recent URLs
$urls = $conn->query("SELECT * FROM urls ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShortLink - URL Shortener</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%); min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 700px; margin: 0 auto; }
        .card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); }
        .header { text-align: center; margin-bottom: 30px; }
        .header i { font-size: 3rem; color: #0ea5e9; margin-bottom: 15px; }
        .header h1 { font-size: 2rem; color: #1f2937; }
        .header p { color: #6b7280; margin-top: 10px; }
        .input-group { display: flex; gap: 10px; margin-bottom: 20px; }
        .input-group input { flex: 1; padding: 15px 20px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1rem; }
        .input-group input:focus { outline: none; border-color: #0ea5e9; }
        .input-group button { padding: 15px 30px; background: #0ea5e9; color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 1rem; }
        .input-group button:hover { background: #0284c7; }
        .result { background: #f0f9ff; padding: 20px; border-radius: 12px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .result-url { font-weight: 600; color: #0ea5e9; word-break: break-all; }
        .copy-btn { background: #0ea5e9; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .table-container { margin-top: 30px; }
        .table-container h3 { margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; color: #6b7280; font-size: 0.85rem; }
        .url-cell { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .clicks-badge { background: #dbeafe; color: #1d4ed8; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .short-link { color: #0ea5e9; text-decoration: none; font-weight: 500; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: rgba(255,255,255,0.8); text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <i class="fas fa-link"></i>
                <h1>ShortLink</h1>
                <p>Shorten your URLs and track clicks</p>
            </div>

            <?php if ($message === 'invalid'): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Please enter a valid URL</div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <input type="url" name="url" placeholder="Paste your long URL here..." required>
                    <button type="submit" name="shorten"><i class="fas fa-bolt"></i> Shorten</button>
                </div>
            </form>

            <?php if ($newUrl): ?>
                <div class="result">
                    <span class="result-url" id="shortUrl"><?php echo $newUrl; ?></span>
                    <button class="copy-btn" onclick="copyUrl()"><i class="fas fa-copy"></i> Copy</button>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <h3><i class="fas fa-history"></i> Recent Links</h3>
                <table>
                    <thead>
                        <tr><th>Short Link</th><th>Original URL</th><th>Clicks</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $urls->fetch_assoc()): ?>
                            <tr>
                                <td><a href="index.php?r=<?php echo $u['short_code']; ?>" class="short-link" target="_blank">/<?php echo $u['short_code']; ?></a></td>
                                <td class="url-cell" title="<?php echo htmlspecialchars($u['original_url']); ?>"><?php echo htmlspecialchars($u['original_url']); ?></td>
                                <td><span class="clicks-badge"><?php echo $u['clicks']; ?> clicks</span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <a href="../../index.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
    </div>

    <script>
        function copyUrl() {
            navigator.clipboard.writeText(document.getElementById('shortUrl').textContent);
            event.target.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(() => event.target.innerHTML = '<i class="fas fa-copy"></i> Copy', 2000);
        }
    </script>
</body>
</html>
