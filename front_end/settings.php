<?php
// Start session and verify user authentication
session_start();
require_once __DIR__ . '/../back_end/auth/auth_check.php';

$firstName = htmlspecialchars($_SESSION["first_name"] ?? "");
$lastName = htmlspecialchars($_SESSION["last_name"] ?? "");
$role = htmlspecialchars($_SESSION["role"] ?? "caregiver");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Visual Schedule System</title>
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
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">Overview</a>
                <a href="children.php" class="nav-item">Children Profiles</a>
                <a href="schedule.php" class="nav-item">Visual Schedules</a>
                <a href="task.php" class="nav-item">Task Library</a>
                <a href="progress.php" class="nav-item">Progress Logs</a>
                <a href="communication.php" class="nav-item">Communication</a>
                <a href="child_view.php" class="nav-item">Child View Mode</a>
                <a href="settings.php" class="nav-item active">Settings</a>
            </nav>
            <div class="sidebar-footer">
                <a href="../back_end/auth/logout.php" class="logout-btn">Log Out</a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">

            <header class="dashboard-header">
                <div>
                    <h1>Account & System Settings</h1>
                    <p class="subtitle">Update your profile details and notification preferences.</p>
                </div>
            </header>

            <!-- Profile Information Form -->
            <section class="dashboard-panel">
                <div class="panel-header">
                    <h2>Profile Information</h2>
                </div>
                <form action="#" method="POST" class="grid-form">
                    <div class="form-group">
                        <label for="set_first_name">First Name</label>
                        <input type="text" id="set_first_name" name="first_name" value="<?= $firstName ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="set_last_name">Last Name</label>
                        <input type="text" id="set_last_name" name="last_name" value="<?= $lastName ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="set_role">Account Role</label>
                        <input type="text" id="set_role" value="<?= ucfirst($role) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="sound_cues">Child View Sound Effects</label>
                        <select id="sound_cues" name="sound_cues">
                            <option value="enabled">Enabled (Gentle Chime)</option>
                            <option value="disabled">Muted</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </section>

        </main>

    </div>

</body>
</html>
