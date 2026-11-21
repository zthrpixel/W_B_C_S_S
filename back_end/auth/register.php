<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../front_end/register.php");
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
    exit("Please fill in all required fields.");
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Please enter a valid email address.");
}

// Validate username
if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
    exit("Username must be 3-50 characters and contain only letters, numbers, and underscores.");
}

// Validate password
if (strlen($password) < 8) {
    exit("Password must be at least 8 characters long.");
}

// Confirm password
if ($password !== $confirm_password) {
    exit("Passwords do not match.");
}

// Validate role
$allowed_roles = ["caregiver", "therapist", "teacher"];

if (!in_array($role, $allowed_roles, true)) {
    exit("Invalid user role.");
}

// Validate optional age
$age = null;

if ($age_input !== "") {
    if (!ctype_digit($age_input) || (int)$age_input > 120) {
        exit("Please enter a valid age.");
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
        exit("That username or email is already registered.");
    }

    error_log($e->getMessage());
    exit("Registration failed. Please try again.");
}
?>
