<?php
/**
 * Single Post View - Blog CMS
 * @author Afzal Khan
 */
require_once 'includes/db.php';

if (!isset($_GET['slug'])) { header('Location: index.php'); exit; }

$slug = $conn->real_escape_string($_GET['slug']);
$result = $conn->query("SELECT p.*, c.name as category_name, c.id as category_id FROM posts p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = '$slug' AND p.status = 'published'");

if ($result->num_rows == 0) { header('Location: index.php'); exit; }
$post = $result->fetch_assoc();

// Increment views
$conn->query("UPDATE posts SET views = views + 1 WHERE id = " . $post['id']);

// Handle comment submission
$comment_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $content = $conn->real_escape_string(trim($_POST['content']));
    if ($name && $content) {
        $conn->query("INSERT INTO comments (post_id, author_name, author_email, content, status) VALUES ({$post['id']}, '$name', '$email', '$content', 'approved')");
        $comment_msg = 'Comment added!';
    }
}

// Get comments
$comments = $conn->query("SELECT * FROM comments WHERE post_id = {$post['id']} AND status = 'approved' ORDER BY created_at DESC");

// Related posts
$related = $conn->query("SELECT id, title, slug, featured_image FROM posts WHERE category_id = {$post['category_id']} AND id != {$post['id']} AND status = 'published' LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($post['title']); ?> | DevBlog</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo"><i class="fas fa-code"></i> DevBlog</a>
                <nav class="nav">
                    <a href="index.php" class="nav-link">Blog</a>
                    <a href="admin/" class="nav-link">Admin</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <article class="single-post">
            <div class="container container-narrow">
                <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Blog</a>
                
                <?php if ($post['featured_image']): ?>
                    <img src="<?php echo e($post['featured_image']); ?>" alt="<?php echo e($post['title']); ?>" class="featured-image">
                <?php endif; ?>
                
                <div class="post-header">
                    <a href="index.php?category=<?php echo $post['category_id']; ?>" class="category-badge">
                        <?php echo e($post['category_name']); ?>
                    </a>
                    <h1><?php echo e($post['title']); ?></h1>
                    <div class="post-meta-single">
                        <span><i class="far fa-calendar"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                        <span><i class="far fa-eye"></i> <?php echo $post['views']; ?> views</span>
                        <span><i class="far fa-comment"></i> <?php echo $comments->num_rows; ?> comments</span>
                    </div>
                </div>

                <div class="post-body">
                    <?php echo $post['content']; ?>
                </div>

                <!-- Comments Section -->
                <section class="comments-section">
                    <h3><i class="fas fa-comments"></i> Comments (<?php echo $comments->num_rows; ?>)</h3>
                    
                    <?php if ($comment_msg): ?>
                        <div class="alert alert-success"><?php echo $comment_msg; ?></div>
                    <?php endif; ?>

                    <!-- Comment Form -->
                    <form method="POST" class="comment-form">
                        <div class="form-row">
                            <input type="text" name="name" placeholder="Your Name *" required>
                            <input type="email" name="email" placeholder="Email (optional)">
                        </div>
                        <textarea name="content" placeholder="Write a comment..." required></textarea>
                        <button type="submit" name="add_comment" class="btn btn-primary">Post Comment</button>
                    </form>

                    <!-- Comments List -->
                    <div class="comments-list">
                        <?php if ($comments->num_rows > 0): ?>
                            <?php while ($comment = $comments->fetch_assoc()): ?>
                                <div class="comment">
                                    <div class="comment-avatar"><?php echo strtoupper(substr($comment['author_name'], 0, 1)); ?></div>
                                    <div class="comment-body">
                                        <div class="comment-header">
                                            <strong><?php echo e($comment['author_name']); ?></strong>
                                            <span class="comment-date"><?php echo timeAgo($comment['created_at']); ?></span>
                                        </div>
                                        <p><?php echo nl2br(e($comment['content'])); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="no-comments">No comments yet. Be the first!</p>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Related Posts -->
                <?php if ($related->num_rows > 0): ?>
                    <section class="related-posts">
                        <h3>Related Articles</h3>
                        <div class="related-grid">
                            <?php while ($rel = $related->fetch_assoc()): ?>
                                <a href="post.php?slug=<?php echo e($rel['slug']); ?>" class="related-card">
                                    <img src="<?php echo e($rel['featured_image']); ?>" alt="">
                                    <span><?php echo e($rel['title']); ?></span>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </article>
    </main>

    <footer class="footer">
        <div class="container"><p>&copy; 2026 DevBlog | Afzal Khan</p></div>
    </footer>
</body>
</html>
