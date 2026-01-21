<?php
/**
 * =============================================================================
 * TASK MANAGER - MAIN APPLICATION
 * =============================================================================
 * 
 * Complete task management application with:
 * - Task CRUD operations
 * - Category filtering
 * - Priority and status management
 * - Due date tracking
 * - Responsive design
 * 
 * @author  Afzal Khan
 * @version 1.0.0
 * @since   January 2026
 * =============================================================================
 */

require_once 'includes/db.php';

// -----------------------------------------------------------------------------
// Handle Task Actions (Create, Update, Delete, Toggle Status)
// -----------------------------------------------------------------------------

$message = '';
$messageType = '';

// DELETE Task
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM tasks WHERE id = $id")) {
        $message = 'Task deleted successfully!';
        $messageType = 'success';
    }
}

// TOGGLE Status (Quick complete/uncomplete)
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $result = $conn->query("SELECT status FROM tasks WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        $newStatus = ($row['status'] === 'completed') ? 'pending' : 'completed';
        $conn->query("UPDATE tasks SET status = '$newStatus' WHERE id = $id");
        $message = ($newStatus === 'completed') ? 'Task completed!' : 'Task reopened!';
        $messageType = 'success';
    }
}

// CREATE or UPDATE Task
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : 'NULL';
    $priority = $conn->real_escape_string($_POST['priority'] ?? 'medium');
    $status = $conn->real_escape_string($_POST['status'] ?? 'pending');
    $due_date = !empty($_POST['due_date']) ? "'" . $conn->real_escape_string($_POST['due_date']) . "'" : 'NULL';
    
    if (!empty($title)) {
        if (!empty($_POST['task_id'])) {
            // UPDATE
            $id = (int)$_POST['task_id'];
            $sql = "UPDATE tasks SET 
                    title = '$title', 
                    description = '$description', 
                    category_id = $category_id, 
                    priority = '$priority', 
                    status = '$status', 
                    due_date = $due_date 
                    WHERE id = $id";
            $message = 'Task updated successfully!';
        } else {
            // CREATE
            $sql = "INSERT INTO tasks (title, description, category_id, priority, status, due_date) 
                    VALUES ('$title', '$description', $category_id, '$priority', '$status', $due_date)";
            $message = 'Task created successfully!';
        }
        
        if ($conn->query($sql)) {
            $messageType = 'success';
        } else {
            $message = 'Error: ' . $conn->error;
            $messageType = 'error';
        }
    }
}

// -----------------------------------------------------------------------------
// Fetch Data for Display
// -----------------------------------------------------------------------------

// Get filter parameters
$filterCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterPriority = isset($_GET['priority']) ? $_GET['priority'] : '';

// Build query with filters
$whereClause = "WHERE 1=1";
if ($filterCategory > 0) $whereClause .= " AND t.category_id = $filterCategory";
if (!empty($filterStatus)) $whereClause .= " AND t.status = '" . $conn->real_escape_string($filterStatus) . "'";
if (!empty($filterPriority)) $whereClause .= " AND t.priority = '" . $conn->real_escape_string($filterPriority) . "'";

// Get tasks with category info
$tasks = $conn->query("
    SELECT t.*, c.name as category_name, c.color as category_color 
    FROM tasks t 
    LEFT JOIN categories c ON t.category_id = c.id 
    $whereClause 
    ORDER BY 
        CASE t.status WHEN 'completed' THEN 1 ELSE 0 END,
        CASE t.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END,
        t.due_date ASC
");

// Get categories for filter/select
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$categoriesList = [];
while ($cat = $categories->fetch_assoc()) {
    $categoriesList[] = $cat;
}

// Get task for editing
$editTask = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM tasks WHERE id = $editId");
    $editTask = $result->fetch_assoc();
}

// Stats
$statsResult = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN due_date < CURDATE() AND status != 'completed' THEN 1 ELSE 0 END) as overdue
    FROM tasks");
$stats = $statsResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager | Afzal Khan Portfolio</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- ================================================================
         HEADER
         ================================================================ -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-tasks"></i>
                    <span>TaskMaster</span>
                </div>
                <a href="../../index.html" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Portfolio
                </a>
            </div>
        </div>
    </header>

    <!-- ================================================================
         MAIN CONTENT
         ================================================================ -->
    <main class="main">
        <div class="container">
            
            <!-- Alert Message -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo e($message); ?>
                </div>
            <?php endif; ?>

            <div class="app-grid">
                <!-- =============================================================
                     SIDEBAR - Stats, Filters, Add Task
                     ============================================================= -->
                <aside class="sidebar">
                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['total']; ?></div>
                            <div class="stat-label">Total Tasks</div>
                        </div>
                        <div class="stat-card stat-completed">
                            <div class="stat-number"><?php echo $stats['completed']; ?></div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="stat-card stat-pending">
                            <div class="stat-number"><?php echo $stats['pending']; ?></div>
                            <div class="stat-label">Pending</div>
                        </div>
                        <div class="stat-card stat-overdue">
                            <div class="stat-number"><?php echo $stats['overdue']; ?></div>
                            <div class="stat-label">Overdue</div>
                        </div>
                    </div>

                    <!-- Add/Edit Task Form -->
                    <div class="card">
                        <h3 class="card-title">
                            <i class="fas fa-<?php echo $editTask ? 'edit' : 'plus'; ?>"></i>
                            <?php echo $editTask ? 'Edit Task' : 'Add New Task'; ?>
                        </h3>
                        <form method="POST" action="index.php">
                            <?php if ($editTask): ?>
                                <input type="hidden" name="task_id" value="<?php echo $editTask['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="title">Task Title *</label>
                                <input type="text" id="title" name="title" required
                                       placeholder="What needs to be done?"
                                       value="<?php echo $editTask ? e($editTask['title']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" 
                                          placeholder="Add details..."><?php echo $editTask ? e($editTask['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select id="category_id" name="category_id">
                                        <option value="">No Category</option>
                                        <?php foreach ($categoriesList as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>"
                                                    <?php echo ($editTask && $editTask['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                <?php echo e($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="priority">Priority</label>
                                    <select id="priority" name="priority">
                                        <option value="low" <?php echo ($editTask && $editTask['priority'] === 'low') ? 'selected' : ''; ?>>Low</option>
                                        <option value="medium" <?php echo (!$editTask || $editTask['priority'] === 'medium') ? 'selected' : ''; ?>>Medium</option>
                                        <option value="high" <?php echo ($editTask && $editTask['priority'] === 'high') ? 'selected' : ''; ?>>High</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="due_date">Due Date</label>
                                    <input type="date" id="due_date" name="due_date"
                                           value="<?php echo $editTask ? $editTask['due_date'] : ''; ?>">
                                </div>
                                
                                <?php if ($editTask): ?>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status">
                                        <option value="pending" <?php echo $editTask['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in_progress" <?php echo $editTask['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="completed" <?php echo $editTask['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i>
                                    <?php echo $editTask ? 'Update Task' : 'Add Task'; ?>
                                </button>
                                <?php if ($editTask): ?>
                                    <a href="index.php" class="btn btn-outline btn-block">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- Filters -->
                    <div class="card">
                        <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
                        <form method="GET" action="index.php">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categoriesList as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"
                                                <?php echo $filterCategory == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo e($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $filterStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in_progress" <?php echo $filterStatus === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $filterStatus === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" onchange="this.form.submit()">
                                    <option value="">All Priorities</option>
                                    <option value="high" <?php echo $filterPriority === 'high' ? 'selected' : ''; ?>>High</option>
                                    <option value="medium" <?php echo $filterPriority === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="low" <?php echo $filterPriority === 'low' ? 'selected' : ''; ?>>Low</option>
                                </select>
                            </div>
                            <?php if ($filterCategory || $filterStatus || $filterPriority): ?>
                                <a href="index.php" class="btn btn-outline btn-sm btn-block">Clear Filters</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </aside>

                <!-- =============================================================
                     MAIN CONTENT - Task List
                     ============================================================= -->
                <section class="task-list-section">
                    <div class="section-header">
                        <h2>
                            <i class="fas fa-list-check"></i> 
                            My Tasks
                            <span class="task-count"><?php echo $tasks->num_rows; ?> tasks</span>
                        </h2>
                    </div>

                    <div class="task-list">
                        <?php if ($tasks->num_rows > 0): ?>
                            <?php while ($task = $tasks->fetch_assoc()): ?>
                                <div class="task-item <?php echo $task['status'] === 'completed' ? 'task-completed' : ''; ?>">
                                    <!-- Checkbox -->
                                    <a href="index.php?toggle=<?php echo $task['id']; ?>" class="task-checkbox">
                                        <i class="<?php echo $task['status'] === 'completed' ? 'fas fa-check-circle' : 'far fa-circle'; ?>"></i>
                                    </a>
                                    
                                    <!-- Task Content -->
                                    <div class="task-content">
                                        <div class="task-header">
                                            <h4 class="task-title"><?php echo e($task['title']); ?></h4>
                                            <span class="badge <?php echo getPriorityClass($task['priority']); ?>">
                                                <?php echo ucfirst($task['priority']); ?>
                                            </span>
                                        </div>
                                        
                                        <?php if ($task['description']): ?>
                                            <p class="task-description"><?php echo e($task['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="task-meta">
                                            <?php if ($task['category_name']): ?>
                                                <span class="meta-item" style="color: <?php echo $task['category_color']; ?>">
                                                    <i class="fas fa-tag"></i> <?php echo e($task['category_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($task['due_date']): ?>
                                                <?php 
                                                $dueClass = '';
                                                if ($task['status'] !== 'completed') {
                                                    $dueDate = strtotime($task['due_date']);
                                                    if ($dueDate < strtotime('today')) $dueClass = 'overdue';
                                                    elseif ($dueDate == strtotime('today')) $dueClass = 'due-today';
                                                }
                                                ?>
                                                <span class="meta-item <?php echo $dueClass; ?>">
                                                    <i class="fas fa-calendar"></i> <?php echo formatDate($task['due_date']); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <span class="meta-item badge <?php echo getStatusClass($task['status']); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="task-actions">
                                        <a href="index.php?edit=<?php echo $task['id']; ?>" class="btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?delete=<?php echo $task['id']; ?>" class="btn-icon btn-danger" 
                                           title="Delete" onclick="return confirm('Delete this task?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list"></i>
                                <h3>No tasks found</h3>
                                <p>Add a new task to get started!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <!-- ================================================================
         FOOTER
         ================================================================ -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Task Manager | Built by <a href="../../index.html">Afzal Khan</a></p>
        </div>
    </footer>

    <script src="js/app.js"></script>
</body>
</html>
