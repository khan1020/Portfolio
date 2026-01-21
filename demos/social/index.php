<?php
/**
 * Mini Social Media Platform
 * @author Afzal Khan
 */
session_start();
$conn = new mysqli("localhost", "root", "");
$conn->query("CREATE DATABASE IF NOT EXISTS social_media_db");
$conn->select_db("social_media_db");

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100),
    avatar VARCHAR(255),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    user_id INT,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    user_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id)
)");

// Sample data
if ($conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'] == 0) {
    $conn->query("INSERT INTO users (username, name, avatar, bio) VALUES 
        ('johndoe', 'John Doe', 'https://i.pravatar.cc/150?img=3', 'Full-stack developer | Coffee lover ☕'),
        ('sarahdev', 'Sarah Developer', 'https://i.pravatar.cc/150?img=5', 'UI/UX enthusiast | Building cool stuff'),
        ('alextech', 'Alex Tech', 'https://i.pravatar.cc/150?img=8', 'Tech blogger | Open source contributor')");
    
    $conn->query("INSERT INTO posts (user_id, content, image_url) VALUES 
        (1, 'Just launched my new portfolio website! 🚀 Really proud of how it turned out. Check it out and let me know what you think!', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600'),
        (2, 'Beautiful morning workout session. Starting the day right! 💪 #fitness #motivation', 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=600'),
        (3, 'New blog post: \"10 VS Code Extensions Every Developer Needs\" - Link in bio! 📝', NULL),
        (1, 'Coffee and code. The perfect combination. ☕💻', 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=600')");
    
    $conn->query("INSERT INTO likes (post_id, user_id) VALUES (1,2),(1,3),(2,1),(2,3),(3,1),(4,2),(4,3)");
    $conn->query("INSERT INTO comments (post_id, user_id, content) VALUES (1, 2, 'Looks amazing! Great work! 🔥'), (1, 3, 'Love the design!'), (2, 1, 'Keep it up! 💪')");
}

// Handle login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'johndoe';
}

// Handle new post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_post'])) {
    $content = $conn->real_escape_string(trim($_POST['content']));
    $image = $conn->real_escape_string(trim($_POST['image_url'] ?? ''));
    if ($content) {
        $conn->query("INSERT INTO posts (user_id, content, image_url) VALUES ({$_SESSION['user_id']}, '$content', " . ($image ? "'$image'" : "NULL") . ")");
    }
    header("Location: index.php");
    exit;
}

// Handle like
if (isset($_GET['like'])) {
    $pid = (int)$_GET['like'];
    $conn->query("INSERT IGNORE INTO likes (post_id, user_id) VALUES ($pid, {$_SESSION['user_id']})");
    header("Location: index.php");
    exit;
}

// Handle unlike
if (isset($_GET['unlike'])) {
    $pid = (int)$_GET['unlike'];
    $conn->query("DELETE FROM likes WHERE post_id = $pid AND user_id = {$_SESSION['user_id']}");
    header("Location: index.php");
    exit;
}

// Handle comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $pid = (int)$_POST['post_id'];
    $content = $conn->real_escape_string(trim($_POST['comment']));
    if ($content) {
        $conn->query("INSERT INTO comments (post_id, user_id, content) VALUES ($pid, {$_SESSION['user_id']}, '$content')");
    }
    header("Location: index.php");
    exit;
}

// Get feed
$posts = $conn->query("SELECT p.*, u.username, u.name, u.avatar,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = {$_SESSION['user_id']}) as user_liked
    FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 20");

$currentUser = $conn->query("SELECT * FROM users WHERE id = {$_SESSION['user_id']}")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialHub - Connect & Share</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; min-height: 100vh; }
        .header { background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
        .header .container { max-width: 900px; margin: 0 auto; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: 700; color: #1877f2; display: flex; align-items: center; gap: 10px; }
        .user-menu { display: flex; align-items: center; gap: 15px; }
        .user-menu img { width: 36px; height: 36px; border-radius: 50%; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .create-post { display: flex; gap: 15px; }
        .create-post img { width: 45px; height: 45px; border-radius: 50%; }
        .create-post textarea { flex: 1; border: none; resize: none; font-size: 1rem; padding: 10px; background: #f0f2f5; border-radius: 20px; }
        .create-post textarea:focus { outline: none; background: #e4e6eb; }
        .post-actions { display: flex; gap: 10px; margin-top: 15px; justify-content: flex-end; }
        .post-actions input { padding: 8px 15px; border: 1px solid #ddd; border-radius: 6px; }
        .btn { padding: 10px 20px; background: #1877f2; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn:hover { background: #166fe5; }
        .post-card { padding: 0; }
        .post-header { display: flex; gap: 12px; padding: 15px 20px; }
        .post-header img { width: 45px; height: 45px; border-radius: 50%; }
        .post-header-info h4 { font-size: 0.95rem; }
        .post-header-info p { font-size: 0.8rem; color: #65676b; }
        .post-content { padding: 0 20px 15px; font-size: 0.95rem; line-height: 1.5; }
        .post-image { width: 100%; max-height: 400px; object-fit: cover; }
        .post-stats { display: flex; justify-content: space-between; padding: 10px 20px; border-top: 1px solid #eff2f5; font-size: 0.9rem; color: #65676b; }
        .post-buttons { display: flex; border-top: 1px solid #eff2f5; }
        .post-btn { flex: 1; padding: 12px; text-align: center; color: #65676b; font-weight: 600; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; }
        .post-btn:hover { background: #f0f2f5; }
        .post-btn.liked { color: #1877f2; }
        .post-btn.liked i { color: #1877f2; }
        .comments-section { padding: 15px 20px; border-top: 1px solid #eff2f5; }
        .comment { display: flex; gap: 10px; margin-bottom: 12px; }
        .comment img { width: 32px; height: 32px; border-radius: 50%; }
        .comment-bubble { background: #f0f2f5; padding: 10px 15px; border-radius: 18px; font-size: 0.9rem; }
        .comment-bubble strong { display: block; font-size: 0.85rem; }
        .comment-form { display: flex; gap: 10px; margin-top: 10px; }
        .comment-form input { flex: 1; padding: 10px 15px; border: none; background: #f0f2f5; border-radius: 20px; font-size: 0.9rem; }
        .back-link { display: block; text-align: center; color: #65676b; text-decoration: none; margin-top: 20px; }
    </style>
</head>
<body>
    <header class="header">
        <div class="container" style="max-width: 900px;">
            <div class="logo"><i class="fas fa-share-nodes"></i> SocialHub</div>
            <div class="user-menu">
                <img src="<?php echo $currentUser['avatar']; ?>" alt="">
                <a href="../../index.html" style="color:#65676b; text-decoration:none;"><i class="fas fa-arrow-left"></i> Portfolio</a>
            </div>
        </div>
    </header>

    <div class="container">
        <form method="POST" class="card">
            <div class="create-post">
                <img src="<?php echo $currentUser['avatar']; ?>" alt="">
                <textarea name="content" placeholder="What's on your mind, <?php echo explode(' ', $currentUser['name'])[0]; ?>?" rows="2" required></textarea>
            </div>
            <div class="post-actions">
                <input type="url" name="image_url" placeholder="Image URL (optional)">
                <button type="submit" name="new_post" class="btn"><i class="fas fa-paper-plane"></i> Post</button>
            </div>
        </form>

        <?php while ($post = $posts->fetch_assoc()): ?>
            <div class="card post-card">
                <div class="post-header">
                    <img src="<?php echo $post['avatar']; ?>" alt="">
                    <div class="post-header-info">
                        <h4><?php echo htmlspecialchars($post['name']); ?></h4>
                        <p><?php echo date('M j, Y \a\t g:i A', strtotime($post['created_at'])); ?></p>
                    </div>
                </div>
                <div class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
                <?php if ($post['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="post-image" alt="">
                <?php endif; ?>
                <div class="post-stats">
                    <span><?php echo $post['like_count']; ?> likes</span>
                    <span><?php echo $post['comment_count']; ?> comments</span>
                </div>
                <div class="post-buttons">
                    <?php if ($post['user_liked']): ?>
                        <a href="?unlike=<?php echo $post['id']; ?>" class="post-btn liked"><i class="fas fa-thumbs-up"></i> Liked</a>
                    <?php else: ?>
                        <a href="?like=<?php echo $post['id']; ?>" class="post-btn"><i class="far fa-thumbs-up"></i> Like</a>
                    <?php endif; ?>
                    <span class="post-btn" onclick="document.getElementById('comment-<?php echo $post['id']; ?>').focus()"><i class="far fa-comment"></i> Comment</span>
                </div>
                <div class="comments-section">
                    <?php 
                    $comments = $conn->query("SELECT c.*, u.username, u.name, u.avatar FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = {$post['id']} ORDER BY c.created_at DESC LIMIT 3");
                    while ($c = $comments->fetch_assoc()): ?>
                        <div class="comment">
                            <img src="<?php echo $c['avatar']; ?>" alt="">
                            <div class="comment-bubble">
                                <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                                <?php echo htmlspecialchars($c['content']); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <form method="POST" class="comment-form">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <img src="<?php echo $currentUser['avatar']; ?>" alt="" style="width:32px;height:32px;border-radius:50%;">
                        <input type="text" name="comment" id="comment-<?php echo $post['id']; ?>" placeholder="Write a comment...">
                        <button type="submit" name="add_comment" class="btn" style="padding:8px 15px;"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>

        <a href="../../index.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
    </div>
</body>
</html>
