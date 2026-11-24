<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../front_end/register.php");
    exit();
}

// Send the user back to the form with an error code the page can explain
function fail(string $code): void {
    header("Location: ../../front_end/register.php?error=" . urlencode($code));
    exit();
}

// Get and clean form values
$first_name = trim($_POST["first_name"] ?? "");
$last_name = trim($_POST["last_name"] ?? "");
$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$age_input = trim($_POST["age"] ?? "");
$address = trim($_POST["address"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$role = $_POST["role"] ?? "";

$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";

// Validate required fields
if (
    $first_name === "" ||
    $last_name === "" ||
    $username === "" ||
    $email === "" ||
    $password === "" ||
    $confirm_password === "" ||
    $role === ""
) {
    fail("required");
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail("email");
}

// Validate username
if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
    fail("username");
}

// Validate password
if (strlen($password) < 8) {
    fail("password_length");
}

// Confirm password
if ($password !== $confirm_password) {
    fail("password_mismatch");
}

// Validate role
$allowed_roles = ["caregiver", "therapist", "teacher"];

if (!in_array($role, $allowed_roles, true)) {
    fail("role");
}

// Validate optional age
$age = null;

if ($age_input !== "") {
    if (!ctype_digit($age_input) || (int)$age_input > 120) {
        fail("age");
    }

    $age = (int)$age_input;
}

// Hash the password before saving
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO users
            (first_name, last_name, username, email, age,
             address, password, role, phone)
            VALUES
            (:first_name, :last_name, :username, :email, :age,
             :address, :password, :role, :phone)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":first_name" => $first_name,
        ":last_name" => $last_name,
        ":username" => $username,
        ":email" => $email,
        ":age" => $age,
        ":address" => $address !== "" ? $address : null,
        ":password" => $hashed_password,
        ":role" => $role,
        ":phone" => $phone !== "" ? $phone : null
    ]);

    header("Location: ../../front_end/register.php?status=success");
    exit();

} catch (PDOException $e) {

    // Duplicate username or email
    if ($e->getCode() === "23000") {
        fail("duplicate");
    }

    error_log($e->getMessage());
    fail("server");
}
?>
