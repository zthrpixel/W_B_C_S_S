<?php
// Start session and verify user authentication
session_start();
require_once __DIR__ . '/../back_end/auth/auth_check.php';
require_once __DIR__ . '/../back_end/config/database.php';
require_once __DIR__ . '/partials/helpers.php';

// Retrieve user session details
$firstName = $_SESSION["first_name"] ?? "User";
$role = $_SESSION["role"] ?? "caregiver";
$userId = (int) $_SESSION["user_id"];

// Real figures for the overview cards and today's schedule preview
$childCount = 0;
$schedulesToday = 0;
$tasksTotal = 0;
$tasksCompleted = 0;
$todayItems = [];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM children WHERE user_id = :user_id");
    $stmt->execute([":user_id" => $userId]);
    $childCount = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM schedules s
         INNER JOIN children c ON c.child_id = s.child_id
         WHERE c.user_id = :user_id AND s.schedule_date = CURDATE()"
    );
    $stmt->execute([":user_id" => $userId]);
    $schedulesToday = (int) $stmt->fetchColumn();

    // Same definition of "today's tasks" as the child view and progress pages
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END), 0) AS completed
         FROM tasks t
         INNER JOIN children c ON c.child_id = t.child_id
         WHERE c.user_id = :user_id
           AND (t.due_date IS NULL OR t.due_date = CURDATE())"
    );
    $stmt->execute([":user_id" => $userId]);
    $taskCounts = $stmt->fetch();
    $tasksTotal = (int) $taskCounts["total"];
    $tasksCompleted = (int) $taskCounts["completed"];

    $stmt = $pdo->prepare(
        "SELECT si.start_time, a.activity_name, s.schedule_name,
                c.first_name AS child_first_name
         FROM schedule_items si
         INNER JOIN schedules s ON s.schedule_id = si.schedule_id
         INNER JOIN children c ON c.child_id = s.child_id
         INNER JOIN activities a ON a.activity_id = si.activity_id
         WHERE c.user_id = :user_id AND s.schedule_date = CURDATE()
         ORDER BY si.start_time ASC, si.item_order ASC"
    );
    $stmt->execute([":user_id" => $userId]);
    $todayItems = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
}

$completionPercent = $tasksTotal > 0 ? (int) round($tasksCompleted / $tasksTotal * 100) : 0;
$nowTime = date("H:i:s");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Visual Schedule System</title>
    <!-- Link external stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-body">

    <div class="dashboard-layout">

        <!-- Left Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h2>Visual Schedule</h2>
                <span class="role-badge"><?= e(ucfirst($role)) ?> Portal</span>
            </div>

            <!-- Primary Navigation Links -->
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">Overview</a>
                <a href="children.php" class="nav-item">Children Profiles</a>
                <a href="schedule.php" class="nav-item">Visual Schedules</a>
                <a href="tasks.php" class="nav-item">Tasks</a>
                <a href="progress.php" class="nav-item">Progress Logs</a>
                <a href="communication.php" class="nav-item">Communication</a>
                <a href="child_view.php" class="nav-item">Child View Mode</a>
                <a href="Settings.php" class="nav-item">Settings</a>
            </nav>

            <!-- Logout Action -->
            <div class="sidebar-footer">
                <a href="../back_end/auth/logout.php" class="logout-btn">Log Out</a>
            </div>
        </aside>

        <!-- Main Dashboard Content Area -->
        <main class="main-content">

            <!-- Top Welcome Header -->
            <header class="dashboard-header">
                <div>
                    <h1>Welcome, <?= e($firstName) ?>!</h1>
                    <p class="subtitle">Here is your daily visual schedule overview.</p>
                </div>
                <div class="user-pill">
                    <span>Role: <strong><?= e(ucfirst($role)) ?></strong></span>
                </div>
            </header>

            <!-- Overview Summary Cards -->
            <section class="overview-grid">
                <div class="stat-card">
                    <h3>Schedules Today</h3>
                    <p class="stat-number"><?= $schedulesToday ?></p>
                    <span class="stat-desc">Scheduled for today</span>
                </div>

                <div class="stat-card">
                    <h3>Completed Tasks</h3>
                    <p class="stat-number"><?= $tasksCompleted ?> / <?= $tasksTotal ?></p>
                    <span class="stat-desc">
                        <?= $tasksTotal > 0 ? $completionPercent . "% daily completion" : "No tasks due today" ?>
                    </span>
                </div>

                <div class="stat-card">
                    <h3>Managed Children</h3>
                    <p class="stat-number"><?= $childCount ?></p>
                    <span class="stat-desc">Active learner profiles</span>
                </div>
            </section>

            <!-- Today's Schedule Preview Section -->
            <section class="dashboard-panel">
                <div class="panel-header">
                    <h2>Today's Visual Schedule Preview</h2>
                    <a href="schedule.php" class="btn-secondary">Manage Schedules</a>
                </div>
                <div class="schedule-list">
                    <?php if (!$todayItems): ?>
                        <p class="muted-text">No activities are scheduled for today.</p>
                    <?php endif; ?>

                    <?php foreach ($todayItems as $item): ?>
                        <?php $passed = $item["start_time"] < $nowTime; ?>
                        <div class="schedule-item <?= $passed ? "completed" : "pending" ?>">
                            <span class="time"><?= e(date("h:i A", strtotime($item["start_time"]))) ?></span>
                            <span class="task">
                                <?= e($item["activity_name"]) ?>
                                (<?= e($item["child_first_name"]) ?>)
                            </span>
                            <span class="status-tag <?= $passed ? "done" : "upcoming" ?>">
                                <?= $passed ? "Earlier" : "Upcoming" ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </main>

    </div>

</body>
</html>
