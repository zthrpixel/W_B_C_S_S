<?php
// temporary testing page

session_start();

require_once __DIR__ . '/../back_end/auth/auth_check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>

    <h1>
        Welcome, <?= htmlspecialchars($_SESSION["first_name"]) ?>!
    </h1>

    <p>
        Your role:
        <?= htmlspecialchars($_SESSION["role"]) ?>
    </p>

    <a href="../back_end/auth/logout.php">Log Out</a>

</body>
</html>