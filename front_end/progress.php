<?php
require_once __DIR__ . '/../back_end/auth/auth_check.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Progress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .progress-bar { background: #7ED321; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="tasks.php">Task Manager</a>
            <div class="navbar-nav">
                <a class="nav-link" href="progress.php">Progress</a>
                <a class="nav-link" href="communication.php">Communication</a>
                <a class="nav-link" href="child_view.php">Child View</a>
            </div>
        </div>
    </nav>
    <main class="container py-4">
        <h1 class="h2 mb-4">Progress Tracking</h1>
        <div id="progressAlert" role="alert"></div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label" for="progressChild">Child</label>
                <select class="form-select" id="progressChild"></select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="progressDate">Date</label>
                <input class="form-control" id="progressDate" type="date">
            </div>
        </div>
        <section class="card card-body mb-4">
            <div class="d-flex justify-content-between mb-2">
                <strong id="progressLabel">0 of 0 tasks completed</strong>
                <strong id="progressPercent">0%</strong>
            </div>
            <div class="progress" style="height: 32px">
                <div id="progressBar" class="progress-bar" style="width: 0%">0%</div>
            </div>
        </section>
        <h2 class="h4">Completion History</h2>
        <div id="completionHistory"></div>
    </main>
    <script src="js/script.js"></script>
</body>
</html>
