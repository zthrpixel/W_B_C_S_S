<?php
// Create a child profile for the logged-in user.
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../front_end/index.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../front_end/children.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";

function redirectWith(string $key, string $value): void {
    header("Location: ../../front_end/children.php?" . $key . "=" . urlencode($value));
    exit();
}

$firstName = trim((string) ($_POST["first_name"] ?? ""));
$lastName = trim((string) ($_POST["last_name"] ?? ""));
$dateOfBirth = trim((string) ($_POST["date_of_birth"] ?? ""));
$gender = trim((string) ($_POST["gender"] ?? ""));
$domain = trim((string) ($_POST["development_domain"] ?? ""));
$notes = trim((string) ($_POST["notes"] ?? ""));

// Validate input
if ($firstName === "" || $lastName === "" || $dateOfBirth === "") {
    redirectWith("error", "required");
}
if (strlen($firstName) > 100 || strlen($lastName) > 100 || strlen($gender) > 30 || strlen($domain) > 150) {
    redirectWith("error", "too_long");
}
$dob = DateTime::createFromFormat("Y-m-d", $dateOfBirth);
if (!$dob || $dob->format("Y-m-d") !== $dateOfBirth || $dateOfBirth > date("Y-m-d")) {
    redirectWith("error", "dob");
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO children
            (user_id, first_name, last_name, date_of_birth, gender, development_domain, notes)
         VALUES
            (:user_id, :first_name, :last_name, :date_of_birth, :gender, :development_domain, :notes)"
    );
    $stmt->execute([
        ":user_id" => (int) $_SESSION["user_id"],
        ":first_name" => $firstName,
        ":last_name" => $lastName,
        ":date_of_birth" => $dateOfBirth,
        ":gender" => $gender === "" ? null : $gender,
        ":development_domain" => $domain === "" ? null : $domain,
        ":notes" => $notes === "" ? null : $notes,
    ]);

    redirectWith("status", "created");
} catch (PDOException $e) {
    error_log($e->getMessage());
    redirectWith("error", "server");
}
