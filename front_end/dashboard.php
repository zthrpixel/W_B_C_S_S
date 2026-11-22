<?php
// Start session and verify user authentication
session_start();
require_once __DIR__ . '/../back_end/auth/auth_check.php';

// Retrieve sanitized user session details
$firstName = htmlspecialchars($_SESSION["first_name"] ?? "User");
$role = htmlspecialchars($_SESSION["role"] ?? "caregiver");
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
                <span class="role-badge"><?= ucfirst($role) ?> Portal</span>
            </div>

            <!-- Primary Navigation Links matching project files -->
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">Overview</a>
                <a href="children.php" class="nav-item">Children Profiles</a>
                <a href="schedule.php" class="nav-item">Visual Schedules</a>
                <a href="task.php" class="nav-item">Task Library</a>
                <a href="progress.php" class="nav-item">Progress Logs</a>
                <a href="communication.php" class="nav-item">Communication</a>
                <a href="child_view.php" class="nav-item">Child View Mode</a>
                <a href="settings.php" class="nav-item">Settings</a>
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
                    <h1>Welcome, <?= $firstName ?>!</h1>
                    <p class="subtitle">Here is your daily visual schedule overview.</p>
                </div>
                <div class="user-pill">
                    <span>Role: <strong><?= ucfirst($role) ?></strong></span>
                </div>
            </header>

            <!-- Overview Summary Cards -->
            <section class="overview-grid">
                <div class="stat-card">
                    <h3>Active Schedules</h3>
                    <p class="stat-number">4</p>
                    <span class="stat-desc">Assigned for today</span>
                </div>

                <div class="stat-card">
                    <h3>Completed Tasks</h3>
                    <p class="stat-number">12 / 16</p>
                    <span class="stat-desc">75% daily completion</span>
                </div>

                <div class="stat-card">
                    <h3>Managed Children</h3>
                    <p class="stat-number">3</p>
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
                    <div class="schedule-item completed">
                        <span class="time">08:00 AM</span>
                        <span class="task">Morning Routine & Breakfast</span>
                        <span class="status-tag done">Completed</span>
                    </div>
                    <div class="schedule-item completed">
                        <span class="time">10:00 AM</span>
                        <span class="task">Sensory & Focus Activity</span>
                        <span class="status-tag done">Completed</span>
                    </div>
                    <div class="schedule-item pending">
                        <span class="time">01:30 PM</span>
                        <span class="task">Guided Learning Session</span>
                        <span class="status-tag upcoming">Upcoming</span>
                    </div>
                    <div class="schedule-item pending">
                        <span class="time">04:00 PM</span>
                        <span class="task">Afternoon Wind-Down</span>
                        <span class="status-tag upcoming">Upcoming</span>
                    </div>
                </div>
            </section>

        </main>

    </div>

</body>
</html>
