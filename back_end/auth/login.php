<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../front_end/index.php");
    exit();
}

// Get submitted login information
$login = trim($_POST["login"] ?? "");
$password = $_POST["password"] ?? "";

// Check required fields
if ($login === "" || $password === "") {
    header("Location: ../../front_end/index.php?error=empty");
    exit();
}

try {
    // Allow login using either username or email
    $sql = "SELECT user_id, first_name, last_name,
                   username, email, password, role
            FROM users
            WHERE username = :login OR email = :login
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":login" => $login]);

    $user = $stmt->fetch();

    // Check whether the account exists and password matches
    if (!$user || !password_verify($password, $user["password"])) {
        header("Location: ../../front_end/index.php?error=invalid");
        exit();
    }

    // Create a new session ID after successful login
    session_regenerate_id(true);

    // Store necessary user information in the session
    $_SESSION["user_id"] = $user["user_id"];
    $_SESSION["first_name"] = $user["first_name"];
    $_SESSION["last_name"] = $user["last_name"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["email"] = $user["email"];
    $_SESSION["role"] = $user["role"];

    // Redirect to the dashboard
    header("Location: ../../front_end/dashboard.php");
    exit();

} catch (PDOException $e) {
    error_log($e->getMessage());

    header("Location: ../../front_end/index.php?error=server");
    exit();
}
?>