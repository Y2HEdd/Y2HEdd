<?php
require 'auth.php';

$tasks = null;
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

$query = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$_SESSION['user_id']];
$types = "i";

if ($filter === 'done') {
    $query .= " AND is_done = 1";
} elseif ($filter === 'pending') {
    $query .= " AND is_done = 0";
}

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $types .= "ss";
}

$query .= " ORDER BY deadline IS NULL, deadline ASC, created_at DESC";

$stmt = $mysqli->prepare($query);
if (!$stmt) die("Prepare failed: " . $mysqli->error);
$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) die("Execute failed: " . $stmt->error);
$tasks = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work Request List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .strike { text-decoration: line-through; color: #6c757d; }
        textarea { resize: vertical; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">📝 Work Request List</h2>
        <a href="logout.php" class="btn btn-outline-dark">Logout</a>
    </div>

    <!-- Search & Filter -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-5 col-sm-12">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search task title or description...">
        </div>
        <div class="col-md-3 col-sm-6">
            <select name="filter" class="form-select" onchange="this.form.submit()">
                <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Tasks</option>
                <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="done" <?= $filter === 'done' ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>
        <div class="col-md-2 col-sm-6">
            <button class="btn btn-primary w-100">Apply</button>
        </div>
    </form>

    <!-- Add Task Form -->
    <form method="POST" action="add_task.php" class="card card-body mb-5 shadow-sm">
        <div class="row g-2">
            <div class="col-md-6">
                <input type="text" name="title" class="form-control" placeholder="Task Title" required>
            </div>
            <div class="col-md-6">
                <textarea name="description" class="form-control" placeholder="Task Description" rows="3" required></textarea>
            </div>
        </div>
        <div class="row g-2 mt-2">
            <div class="col-md-3">
                <input type="date" name="deadline" class="form-control" min="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
                <select name="priority" class="form-select">
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-success w-100">Add Task</button>
            </div>
        </div>
    </form>

    <!-- Task List -->
    <?php if ($tasks && $tasks->num_rows > 0): ?>
        <ul class="list-group shadow-sm">
            <?php while ($task = $tasks->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start flex-column flex-md-row">
                    <div class="flex-grow-1 pe-3">
                        <h5 class="<?= $task['is_done'] ? 'strike' : '' ?>">
                            <?= htmlspecialchars($task['title']) ?>
                        </h5>
                        <p class="mb-1 <?= $task['is_done'] ? 'strike' : '' ?>">
                            <?= nl2br(htmlspecialchars($task['description'])) ?>
                        </p>
                        <small class="text-muted">
                            <?= $task['deadline'] ? "📅 " . $task['deadline'] : '' ?>
                            <?= " | 🚦 " . $task['priority'] ?>
                        </small>
                    </div>
                    <div class="btn-group btn-group-sm mt-2 mt-md-0">
                        <a href="toggle_task.php?id=<?= $task['id'] ?>" class="btn btn-outline-<?= $task['is_done'] ? 'warning' : 'success' ?>">
                            <?= $task['is_done'] ? 'Undo' : 'Done' ?>
                        </a>
                        <button class="btn btn-outline-danger" onclick="confirmDelete(<?= $task['id'] ?>)">Delete</button>

                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-info text-center">No tasks found.</div>
    <?php endif; ?>

</div>
<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Task Added!',
        showConfirmButton: false,
        timer: 1500
    });
</script>
<?php endif; ?>
<script>
function confirmDelete(taskId) {
    Swal.fire({
        title: 'Delete Task?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete_task.php?id=${taskId}`;
        }
    });
}
</script>


<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
