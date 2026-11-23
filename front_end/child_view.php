<?php
// Require login before rendering the simplified child view.
require_once __DIR__ . '/../back_end/auth/auth_check.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Day</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 20px; background: #f7fbff; }
        .bg-primary { background-color: #4A90D9 !important; }
        .min-touch { min-height: 44px; }
        .encouragement { color: #37546f; font-size: 1.35rem; }
        .child-progress { height: 38px; border-radius: 12px; background: #dcecfb; }
        .progress-bar { background: #7ED321; font-size: 1rem; font-weight: 700; }
        .schedule-grid, .task-grid { display: grid; gap: 16px; }
        .schedule-card, .child-task-card { border-radius: 12px; background: white; padding: 18px; box-shadow: 0 2px 10px rgba(74,144,217,.12); }
        .schedule-card { display: grid; gap: 6px; border-left: 8px solid #F5A623; }
        .child-task-card { display: flex; align-items: center; justify-content: space-between; gap: 16px; min-height: 120px; border: 3px solid #4A90D9; }
        .child-task-card.completed { border-color: #7ED321; background: #f4fff2; }
        .child-task-card h3 { margin-bottom: 6px; font-size: 1.35rem; }
        .child-task-card p { margin: 0; }
        .done-button { min-width: 120px; min-height: 64px; border-radius: 12px; background: #7ED321; border: 0; font-size: 1.35rem; font-weight: 700; }
        .done-badge { color: #4b8f11; font-weight: 700; font-size: 1.25rem; }
        @media (max-width: 575px) {
            .child-task-card { align-items: stretch; flex-direction: column; }
            .done-button { width: 100%; }
        }
    </style>
</head>
<body class="child-view-page">
    <!-- A caregiver can always return to the regular task interface. -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="tasks.php">My Day</a>
            <a class="btn btn-light min-touch" href="tasks.php">Caregiver View</a>
        </div>
    </nav>

    <main class="container py-4">
        <section class="mb-4">
            <label class="form-label h5" for="childViewPicker">Choose a child</label>
            <select class="form-select form-select-lg min-touch" id="childViewPicker"></select>
        </section>

        <div id="childViewAlert" role="alert"></div>

        <section id="childGreeting" class="text-center mb-4" hidden>
            <h1 id="childName" class="display-6"></h1>
            <p id="encouragement" class="encouragement">You can do it! One step at a time.</p>
        </section>

        <section id="progressSection" class="mb-4" hidden>
            <div class="d-flex justify-content-between mb-2 h5">
                <span>Today's progress</span>
                <strong id="progressText">0%</strong>
            </div>
            <div class="progress child-progress" role="progressbar" aria-label="Today's task progress">
                <div id="childProgressBar" class="progress-bar" style="width: 0%">0%</div>
            </div>
        </section>

        <section class="mb-4" id="scheduleSection" hidden>
            <h2 class="h3 mb-3">Today's Schedule</h2>
            <div id="todaySchedule" class="schedule-grid"></div>
        </section>

        <section id="tasksSection" hidden>
            <h2 class="h3 mb-3">Today's Tasks</h2>
            <div id="childTaskList" class="task-grid"></div>
        </section>
    </main>

    <script src="js/script.js"></script>
</body>
</html>
