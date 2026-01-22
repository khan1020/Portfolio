<?php
/**
 * Real-time Chat Application
 * @author Afzal Khan
 */
session_start();
$conn = new mysqli("localhost", "root", "");
$conn->query("CREATE DATABASE IF NOT EXISTS chat_app_db");
$conn->select_db("chat_app_db");

// Auto-create tables
$conn->query("CREATE TABLE IF NOT EXISTS chat_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    color VARCHAR(7) DEFAULT '#3b82f6',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES chat_users(id)
)");

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join'])) {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $color = '#' . substr(md5($username), 0, 6);
    
    $check = $conn->query("SELECT id FROM chat_users WHERE username = '$username'");
    if ($check->num_rows > 0) {
        $user = $check->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
    } else {
        $conn->query("INSERT INTO chat_users (username, color) VALUES ('$username', '$color')");
        $_SESSION['user_id'] = $conn->insert_id;
    }
    $_SESSION['username'] = $username;
    $_SESSION['color'] = $color;
}

// Handle new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message']) && isset($_SESSION['user_id'])) {
    $msg = $conn->real_escape_string(trim($_POST['message']));
    if ($msg) {
        $conn->query("INSERT INTO messages (user_id, message) VALUES ({$_SESSION['user_id']}, '$msg')");
    }
}

// AJAX: Get messages
if (isset($_GET['get_messages'])) {
    $lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    $result = $conn->query("SELECT m.*, u.username, u.color FROM messages m JOIN chat_users u ON m.user_id = u.id WHERE m.id > $lastId ORDER BY m.created_at ASC LIMIT 50");
    $messages = [];
    while ($row = $result->fetch_assoc()) $messages[] = $row;
    header('Content-Type: application/json');
    echo json_encode($messages);
    exit;
}

$loggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatRoom | Real-time Messaging</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .chat-container { width: 100%; max-width: 800px; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.3); }
        .chat-header { background: #0f172a; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .chat-header h1 { font-size: 1.25rem; display: flex; align-items: center; gap: 10px; }
        .chat-header a { color: rgba(255,255,255,0.7); text-decoration: none; }
        .chat-messages { height: 400px; overflow-y: auto; padding: 20px; background: #f8fafc; }
        .message { display: flex; gap: 12px; margin-bottom: 15px; }
        .message-avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; flex-shrink: 0; }
        .message-content { background: white; padding: 12px 16px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 70%; }
        .message-header { display: flex; gap: 10px; align-items: center; margin-bottom: 5px; }
        .message-username { font-weight: 600; font-size: 0.9rem; }
        .message-time { color: #94a3b8; font-size: 0.75rem; }
        .message-text { color: #334155; line-height: 1.5; }
        .chat-input { display: flex; gap: 10px; padding: 20px; background: white; border-top: 1px solid #e2e8f0; }
        .chat-input input { flex: 1; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; }
        .chat-input input:focus { outline: none; border-color: #3b82f6; }
        .chat-input button { padding: 12px 24px; background: #3b82f6; color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; }
        .login-form { padding: 60px 40px; text-align: center; }
        .login-form h2 { margin-bottom: 10px; color: #0f172a; }
        .login-form p { color: #64748b; margin-bottom: 30px; }
        .login-form input { width: 100%; padding: 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; margin-bottom: 15px; }
        .login-form button { width: 100%; padding: 15px; background: #3b82f6; color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; }
        .user-info { display: flex; align-items: center; gap: 10px; font-size: 0.9rem; }
        .user-badge { background: #10b981; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h1><i class="fas fa-comments"></i> ChatRoom</h1>
            <?php if ($loggedIn): ?>
                <div class="user-info">
                    <span class="user-badge"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="?logout=1"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            <?php else: ?>
                <a href="../../index.html"><i class="fas fa-arrow-left"></i> Portfolio</a>
            <?php endif; ?>
        </div>

        <?php if (!$loggedIn): ?>
            <form method="POST" class="login-form">
                <h2>Join the Chat</h2>
                <p>Enter a username to start chatting</p>
                <input type="text" name="username" placeholder="Your username" required maxlength="20">
                <button type="submit" name="join"><i class="fas fa-sign-in-alt"></i> Join Chat</button>
            </form>
        <?php else: ?>
            <div class="chat-messages" id="messages"></div>
            <form method="POST" class="chat-input" id="messageForm">
                <input type="text" name="message" id="messageInput" placeholder="Type a message..." autocomplete="off">
                <button type="submit" name="send_message"><i class="fas fa-paper-plane"></i></button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($loggedIn): ?>
    <script>
        let lastId = 0;
        const messagesDiv = document.getElementById('messages');
        const form = document.getElementById('messageForm');
        const input = document.getElementById('messageInput');

        function loadMessages() {
            fetch(`index.php?get_messages=1&last_id=${lastId}`)
                .then(r => r.json())
                .then(messages => {
                    messages.forEach(msg => {
                        if (msg.id > lastId) lastId = parseInt(msg.id);
                        const time = new Date(msg.created_at).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'});
                        messagesDiv.innerHTML += `
                            <div class="message">
                                <div class="message-avatar" style="background:${msg.color}">${msg.username.charAt(0).toUpperCase()}</div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <span class="message-username" style="color:${msg.color}">${msg.username}</span>
                                        <span class="message-time">${time}</span>
                                    </div>
                                    <div class="message-text">${msg.message}</div>
                                </div>
                            </div>
                        `;
                    });
                    if (messages.length > 0) messagesDiv.scrollTop = messagesDiv.scrollHeight;
                });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!input.value.trim()) return;
            
            fetch('index.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `send_message=1&message=${encodeURIComponent(input.value)}`
            }).then(() => {
                input.value = '';
                loadMessages();
            });
        });

        loadMessages();
        setInterval(loadMessages, 2000); // Poll every 2 seconds
    </script>
    <?php endif; ?>
</body>
</html>
<?php if (isset($_GET['logout'])) { session_destroy(); header('Location: index.php'); exit; } ?>
