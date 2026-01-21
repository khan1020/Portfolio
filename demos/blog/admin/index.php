<?php
/**
 * Blog CMS Admin Panel
 * @author Afzal Khan
 */
require_once '../includes/db.php';

// Handle actions
$msg = '';
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM posts WHERE id = $id")) $msg = 'Post deleted!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $content = $conn->real_escape_string($_POST['content']);
    $excerpt = $conn->real_escape_string(trim($_POST['excerpt']));
    $image = $conn->real_escape_string(trim($_POST['featured_image']));
    $cat = (int)$_POST['category_id'];
    $status = $_POST['status'] === 'published' ? 'published' : 'draft';
    $postSlug = slug($title);
    
    if (!empty($_POST['post_id'])) {
        $id = (int)$_POST['post_id'];
        $conn->query("UPDATE posts SET title='$title', slug='$postSlug', content='$content', excerpt='$excerpt', featured_image='$image', category_id=$cat, status='$status' WHERE id=$id");
        $msg = 'Post updated!';
    } else {
        $conn->query("INSERT INTO posts (title, slug, content, excerpt, featured_image, category_id, status) VALUES ('$title', '$postSlug', '$content', '$excerpt', '$image', $cat, '$status')");
        $msg = 'Post created!';
    }
}

$editPost = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editPost = $conn->query("SELECT * FROM posts WHERE id = $id")->fetch_assoc();
}

$posts = $conn->query("SELECT p.*, c.name as category_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$catList = []; while ($c = $categories->fetch_assoc()) $catList[] = $c;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - DevBlog</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; min-height: 100vh; }
        .header { background: #1f2937; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.25rem; display: flex; align-items: center; gap: 10px; }
        .header a { color: white; text-decoration: none; opacity: 0.8; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .grid { display: grid; grid-template-columns: 400px 1fr; gap: 20px; }
        .card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h2 { font-size: 1.1rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 0.9rem; color: #374151; }
        input, select, textarea { width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 0.95rem; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #3b82f6; }
        textarea { height: 200px; resize: vertical; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-sm { padding: 5px 10px; font-size: 0.8rem; }
        .btn-block { width: 100%; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; font-size: 0.85rem; color: #6b7280; }
        .status { padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        .status-published { background: #d1fae5; color: #065f46; }
        .status-draft { background: #fef3c7; color: #92400e; }
        .actions { display: flex; gap: 5px; }
        .alert { padding: 12px; background: #d1fae5; color: #065f46; border-radius: 6px; margin-bottom: 15px; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header class="header">
        <h1><i class="fas fa-newspaper"></i> Blog Admin</h1>
        <a href="../index.php"><i class="fas fa-arrow-left"></i> View Blog</a>
    </header>

    <div class="container">
        <?php if ($msg): ?><div class="alert"><?php echo $msg; ?></div><?php endif; ?>
        
        <div class="grid">
            <div class="card">
                <h2><i class="fas fa-<?php echo $editPost ? 'edit' : 'plus'; ?>"></i> <?php echo $editPost ? 'Edit Post' : 'New Post'; ?></h2>
                <form method="POST">
                    <?php if ($editPost): ?><input type="hidden" name="post_id" value="<?php echo $editPost['id']; ?>"><?php endif; ?>
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" required value="<?php echo $editPost ? e($editPost['title']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Featured Image URL</label>
                        <input type="url" name="featured_image" placeholder="https://..." value="<?php echo $editPost ? e($editPost['featured_image']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id">
                            <?php foreach ($catList as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($editPost && $editPost['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Excerpt</label>
                        <textarea name="excerpt" rows="2" placeholder="Short description..."><?php echo $editPost ? e($editPost['excerpt']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Content (HTML supported)</label>
                        <textarea name="content" required placeholder="Write your post..."><?php echo $editPost ? e($editPost['content']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="draft" <?php echo ($editPost && $editPost['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo (!$editPost || $editPost['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><?php echo $editPost ? 'Update' : 'Publish'; ?> Post</button>
                    <?php if ($editPost): ?><a href="index.php" style="display:block;text-align:center;margin-top:10px;color:#6b7280;">Cancel</a><?php endif; ?>
                </form>
            </div>

            <div class="card">
                <h2><i class="fas fa-list"></i> All Posts (<?php echo $posts->num_rows; ?>)</h2>
                <table>
                    <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Views</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while ($p = $posts->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo e($p['title']); ?></strong></td>
                            <td><?php echo e($p['category_name'] ?? '-'); ?></td>
                            <td><span class="status status-<?php echo $p['status']; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                            <td><?php echo $p['views']; ?></td>
                            <td class="actions">
                                <a href="index.php?edit=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                                <a href="index.php?delete=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
