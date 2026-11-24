
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../back_end/auth/auth_check.php';
require_once __DIR__ . '/../back_end/config/database.php';

$firstName = $_SESSION["first_name"] ?? "";
$lastName = $_SESSION["last_name"] ?? "";
$role = $_SESSION["role"] ?? "caregiver";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $newFirstName = trim($_POST["first_name"] ?? "");
    $newLastName = trim($_POST["last_name"] ?? "");

    if ($newFirstName === "" || $newLastName === "") {

        $message = "Please complete all required fields.";
        $messageType = "error";

    } elseif (strlen($newFirstName) > 100 || strlen($newLastName) > 100) {

        $message = "Names cannot exceed 100 characters.";
        $messageType = "error";

    } else {

        try {

            $stmt = $pdo->prepare("
                UPDATE users
                SET first_name = :first_name,
                    last_name = :last_name
                WHERE user_id = :user_id
            ");

            $stmt->execute([
                ":first_name" => $newFirstName,
                ":last_name" => $newLastName,
                ":user_id" => $_SESSION["user_id"]
            ]);

            // Update session values immediately
            $_SESSION["first_name"] = $newFirstName;
            $_SESSION["last_name"] = $newLastName;

            $firstName = $newFirstName;
            $lastName = $newLastName;

            $message = "Your profile has been updated successfully!";
            $messageType = "success";

        } catch (PDOException $e) {

            error_log("Settings update error: " . $e->getMessage());

            $message = "Unable to update your profile. Please try again.";
            $messageType = "error";
        }
    }
}

$firstNameSafe = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
$lastNameSafe = htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8');
$roleSafe = htmlspecialchars($role, ENT_QUOTES, 'UTF-8');
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

    <aside class="sidebar">

        <div class="sidebar-brand">
            <h2>Visual Schedule</h2>
            <span class="role-badge"><?= ucfirst($roleSafe) ?> Portal</span>
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

    <main class="main-content">

        <header class="dashboard-header">
            <div>
                <h1>Account & System Settings</h1>
                <p class="subtitle">
                    Manage your account information.
                </p>
            </div>
        </header>

        <section class="dashboard-panel">

            <div class="panel-header">
                <h2>Profile Information</h2>
            </div>

            <?php if ($message !== ""): ?>
                <div class="<?= $messageType === 'success' ? 'success-message' : 'error-message' ?>">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="settings.php" method="POST" class="grid-form">

                <div class="form-group">
                    <label for="set_first_name">First Name</label>
                    <input
                        type="text"
                        id="set_first_name"
                        name="first_name"
                        value="<?= $firstNameSafe ?>"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="set_last_name">Last Name</label>
                    <input
                        type="text"
                        id="set_last_name"
                        name="last_name"
                        value="<?= $lastNameSafe ?>"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="set_role">Account Role</label>
                    <input
                        type="text"
                        id="set_role"
                        value="<?= ucfirst($roleSafe) ?>"
                        disabled
                    >
                </div>

                <div class="form-group full-width">
                    <button type="submit" class="btn-primary">
                        Save Changes
                    </button>
                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>
