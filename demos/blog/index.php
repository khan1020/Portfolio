<?php
/**
 * Blog CMS - Main Blog Page
 * @author Afzal Khan
 * @since January 2026
 */
require_once 'includes/db.php';

$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$where = "WHERE p.status = 'published'";
if ($category_filter) $where .= " AND p.category_id = $category_filter";

$posts = $conn->query("SELECT p.*, c.name as category_name, c.slug as category_slug,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'approved') as comment_count
    FROM posts p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.created_at DESC");

$categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM posts WHERE category_id = c.id AND status = 'published') as post_count FROM categories c ORDER BY name");
$catList = []; while ($c = $categories->fetch_assoc()) $catList[] = $c;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBlog - Tech Articles & Tutorials</title>
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
                    <a href="index.php" class="nav-link active">Blog</a>
                    <a href="admin/" class="nav-link"><i class="fas fa-cog"></i> Admin</a>
                    <a href="../../index.html" class="nav-link">Portfolio</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="hero">
                <h1>DevBlog</h1>
                <p>Exploring technology, programming, and web development</p>
            </div>

            <div class="blog-layout">
                <section class="posts-section">
                    <?php if ($posts->num_rows > 0): ?>
                        <div class="posts-grid">
                            <?php while ($post = $posts->fetch_assoc()): ?>
                                <article class="post-card">
                                    <a href="post.php?slug=<?php echo e($post['slug']); ?>" class="post-image">
                                        <img src="<?php echo e($post['featured_image']); ?>" alt="<?php echo e($post['title']); ?>"
                                             onerror="this.src='https://via.placeholder.com/400x250?text=No+Image'">
                                    </a>
                                    <div class="post-content">
                                        <?php if ($post['category_name']): ?>
                                            <a href="index.php?category=<?php echo $post['category_id']; ?>" class="post-category">
                                                <?php echo e($post['category_name']); ?>
                                            </a>
                                        <?php endif; ?>
                                        <h2 class="post-title">
                                            <a href="post.php?slug=<?php echo e($post['slug']); ?>"><?php echo e($post['title']); ?></a>
                                        </h2>
                                        <p class="post-excerpt"><?php echo e($post['excerpt'] ?: excerpt($post['content'])); ?></p>
                                        <div class="post-meta">
                                            <span><i class="far fa-calendar"></i> <?php echo timeAgo($post['created_at']); ?></span>
                                            <span><i class="far fa-eye"></i> <?php echo $post['views']; ?> views</span>
                                            <span><i class="far fa-comment"></i> <?php echo $post['comment_count']; ?></span>
                                        </div>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-newspaper"></i>
                            <h3>No posts yet</h3>
                            <p>Check back later or <a href="admin/">add some posts</a></p>
                        </div>
                    <?php endif; ?>
                </section>

                <aside class="sidebar">
                    <div class="widget">
                        <h3>Categories</h3>
                        <ul class="category-list">
                            <li><a href="index.php" class="<?php echo !$category_filter ? 'active' : ''; ?>">All Posts</a></li>
                            <?php foreach ($catList as $cat): ?>
                                <li>
                                    <a href="index.php?category=<?php echo $cat['id']; ?>" 
                                       class="<?php echo $category_filter == $cat['id'] ? 'active' : ''; ?>">
                                        <?php echo e($cat['name']); ?>
                                        <span class="count"><?php echo $cat['post_count']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="widget">
                        <h3>About</h3>
                        <p>DevBlog is a demo blog platform built with PHP and MySQL, showcasing a clean, modern design.</p>
                        <a href="../../index.html" class="btn btn-outline btn-sm">View Portfolio</a>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 DevBlog | Built by <a href="../../index.html">Afzal Khan</a></p>
        </div>
    </footer>
</body>
</html>
