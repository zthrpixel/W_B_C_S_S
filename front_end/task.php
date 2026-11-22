<?php
// task.php - page for adding, editing, completing and deleting tasks
require_once __DIR__ . '/../back_end/auth/auth_check.php';
require_once __DIR__ . '/../back_end/config/database.php';

// get the children of the logged in user for the dropdowns
$stmt = $pdo->prepare("SELECT child_id, first_name, last_name FROM children
                       WHERE user_id = :user_id ORDER BY first_name");
$stmt->execute([":user_id" => $_SESSION["user_id"]]);
$children = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tasks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>
    body { background: #f4f8fc; font-size: 16px; }
    .btn, .form-control, .form-select { min-height: 44px; border-radius: 12px; }
    .card { border-radius: 12px; }
    .task-done { opacity: 0.6; }
    .task-done .task-title { text-decoration: line-through; }
  </style>
</head>
<body data-page="tasks">

<!-- navigation bar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background: #4A90D9;">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Visual Schedule</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="children.php">Children</a></li>
        <li class="nav-item"><a class="nav-link" href="schedule.php">Schedule</a></li>
        <li class="nav-item"><a class="nav-link active" href="task.php">Tasks</a></li>
        <li class="nav-item"><a class="nav-link" href="child_view.php">Child View</a></li>
        <li class="nav-item"><a class="nav-link" href="progress.php">Progress</a></li>
        <li class="nav-item"><a class="nav-link" href="communication.php">Communication</a></li>
        <li class="nav-item"><a class="nav-link" href="../back_end/auth/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h1 class="h3 mb-3">Task Management</h1>

  <!-- messages from the script show here -->
  <div id="message"></div>

  <?php if (count($children) === 0) { ?>
    <div class="alert alert-warning">
      You have no children yet. Add one in <a href="children.php">Children</a> first.
    </div>
  <?php } else { ?>

  <!-- add task form -->
  <div class="card mb-4">
    <div class="card-body">
      <h2 class="h5">Add Task</h2>
      <form id="addForm" class="row g-3">
        <div class="col-md-4">
          <label class="form-label" for="child_id">Child</label>
          <select class="form-select" id="child_id" name="child_id" required>
            <?php foreach ($children as $child) { ?>
              <option value="<?php echo $child['child_id']; ?>">
                <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?>
              </option>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-8">
          <label class="form-label" for="title">Title</label>
          <input type="text" class="form-control" id="title" name="title" maxlength="150" required>
        </div>
        <div class="col-12">
          <label class="form-label" for="description">Description (optional)</label>
          <textarea class="form-control" id="description" name="description" rows="2"></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="due_date">Due Date (optional)</label>
          <input type="date" class="form-control" id="due_date" name="due_date">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="priority">Priority</label>
          <select class="form-select" id="priority" name="priority">
            <option value="low">Low</option>
            <option value="normal" selected>Normal</option>
            <option value="high">High</option>
          </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">Add Task</button>
        </div>
      </form>
    </div>
  </div>

  <!-- filters -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <select class="form-select" id="filterChild">
        <option value="">All children</option>
        <?php foreach ($children as $child) { ?>
          <option value="<?php echo $child['child_id']; ?>">
            <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?>
          </option>
        <?php } ?>
      </select>
    </div>
    <div class="col-md-4">
      <select class="form-select" id="filterStatus">
        <option value="">All status</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In progress</option>
        <option value="completed">Completed</option>
      </select>
    </div>
  </div>

  <!-- the task list is filled in by script.js -->
  <div id="taskList"></div>

  <!-- edit task popup -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" id="editForm">
        <div class="modal-header">
          <h2 class="modal-title h5">Edit Task</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="task_id" id="edit_task_id">
          <div class="mb-3">
            <label class="form-label" for="edit_title">Title</label>
            <input type="text" class="form-control" id="edit_title" name="title" maxlength="150" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="edit_description">Description</label>
            <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label" for="edit_due_date">Due Date</label>
            <input type="date" class="form-control" id="edit_due_date" name="due_date">
          </div>
          <div class="mb-3">
            <label class="form-label" for="edit_priority">Priority</label>
            <select class="form-select" id="edit_priority" name="priority">
              <option value="low">Low</option>
              <option value="normal">Normal</option>
              <option value="high">High</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="edit_status">Status</label>
            <select class="form-select" id="edit_status" name="status">
              <option value="pending">Pending</option>
              <option value="in_progress">In progress</option>
              <option value="completed" id="edit_completed_option" disabled>Completed</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>

  <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="javascript/script.js"></script>
</body>
</html>
