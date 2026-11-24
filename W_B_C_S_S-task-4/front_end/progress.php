<?php
// Progress tracking: daily completion summary, saved records and history.
require_once __DIR__ . '/../back_end/auth/auth_check.php';
$activePage = "progress";
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
    <?php require __DIR__ . '/partials/navbar.php'; ?>

    <main class="container py-4">
        <h1 class="h2 mb-4">Progress Tracking</h1>
        <div id="progressAlert" role="alert"></div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label" for="progressChild">Child</label>
                <select class="form-select" id="progressChild">
                    <option value="">All children</option>
                </select>
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

        <form id="saveProgressForm" class="card card-body mb-4">
            <h2 class="h5">Save this day's progress</h2>
            <p class="text-muted mb-2">
                Stores the numbers above for the selected child and date, with optional remarks.
                Saving again for the same day updates the record.
            </p>
            <label class="form-label" for="progressRemarks">Remarks (optional)</label>
            <textarea class="form-control mb-3" id="progressRemarks" rows="2"></textarea>
            <div>
                <button class="btn btn-primary" type="submit" id="saveProgressButton" disabled>
                    Save Progress Record
                </button>
                <span class="text-muted ms-2" id="saveProgressHint">Choose a child to enable saving.</span>
            </div>
        </form>

        <h2 class="h4">Completion History</h2>
        <div id="completionHistory"></div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="javascript/progress.js"></script>
</body>
</html>
