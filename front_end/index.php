<?php
session_start();

// If already logged in, go to dashboard
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Visual Schedule System</title>

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="login-container">
        <?php if (isset($_GET["logout"]) && $_GET["logout"] === "success"): ?>
            <p>You have successfully logged out.</p>
        <?php endif; ?>

        <h1>Welcome Back!</h1>

        <p>Log in to access the Visual Schedule System.</p>

        <?php if (isset($_GET["error"])): ?>

            <p class="error-message">
                <?php
                if ($_GET["error"] === "empty") {
                    echo "Please enter your username/email and password.";
                } elseif ($_GET["error"] === "invalid") {
                    echo "Incorrect username/email or password.";
                } else {
                    echo "Something went wrong. Please try again.";
                }
                ?>
            </p>

        <?php endif; ?>

        <form action="../back_end/auth/login.php" method="POST">

            <label for="login">Username or Email</label>

            <input
                type="text"
                id="login"
                name="login"
                required
            >

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <button type="submit">Log In</button>

        </form>

        <p>
            Don't have an account?
            <a href="register.php">Register here</a>
        </p>

    </div>

</body>
</html>