<?php
require_once __DIR__ . '/../back_end/auth/auth_check.php';
$activePage = "tasks";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .task-card { border-left: 6px solid #4A90D9; }
        .task-meta { display: flex; flex-wrap: wrap; gap: 12px; color: #546675; }
        .min-touch { min-height: 44px; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/partials/navbar.php'; ?>
    <main class="container py-4">
        <h1 class="h2 mb-3">Tasks</h1>
        <div id="taskAlert" role="alert"></div>
        <form id="createTaskForm" class="card card-body mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="taskChild">Child</label>
                    <select class="form-select" id="taskChild" name="child_id" required></select>
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="taskTitle">Title</label>
                    <input class="form-control" id="taskTitle" name="title" maxlength="150" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="taskPriority">Priority</label>
                    <select class="form-select" id="taskPriority" name="priority">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="taskDescription">Description</label>
                    <input class="form-control" id="taskDescription" name="description">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="taskDueDate">Due date</label>
                    <input class="form-control" id="taskDueDate" type="date" name="due_date">
                </div>
            </div>
            <button class="btn btn-primary mt-3" type="submit">Add Task</button>
        </form>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label" for="filterChild">Filter by child</label>
                <select class="form-select" id="filterChild"></select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="filterStatus">Filter by status</label>
                <select class="form-select" id="filterStatus">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>
        <div id="taskList"></div>
    </main>
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editTaskForm">
                    <div class="modal-header">
                        <h2 class="modal-title h5">Edit task</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editTaskId" name="task_id">
                        <label class="form-label" for="editTitle">Title</label>
                        <input class="form-control mb-3" id="editTitle" name="title" maxlength="150" required>
                        <label class="form-label" for="editDescription">Description</label>
                        <input class="form-control mb-3" id="editDescription" name="description">
                        <label class="form-label" for="editDueDate">Due date</label>
                        <input class="form-control mb-3" id="editDueDate" type="date" name="due_date">
                        <label class="form-label" for="editPriority">Priority</label>
                        <select class="form-select mb-3" id="editPriority" name="priority">
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                        </select>
                        <label class="form-label" for="editStatus">Status</label>
                        <select class="form-select" id="editStatus" name="status">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In progress</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="javascript/script.js"></script>
</body>
</html>
