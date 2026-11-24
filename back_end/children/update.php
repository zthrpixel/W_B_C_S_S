<?php
// Update a child profile, but only if it belongs to the logged-in user.
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

$childId = filter_input(INPUT_POST, "child_id", FILTER_VALIDATE_INT);
$firstName = trim((string) ($_POST["first_name"] ?? ""));
$lastName = trim((string) ($_POST["last_name"] ?? ""));
$dateOfBirth = trim((string) ($_POST["date_of_birth"] ?? ""));
$gender = trim((string) ($_POST["gender"] ?? ""));
$domain = trim((string) ($_POST["development_domain"] ?? ""));
$notes = trim((string) ($_POST["notes"] ?? ""));

// Validate input
if (!$childId || $firstName === "" || $lastName === "" || $dateOfBirth === "") {
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
    $userId = (int) $_SESSION["user_id"];

    // Confirm this child belongs to the logged-in user
    $check = $pdo->prepare("SELECT child_id FROM children WHERE child_id = :child_id AND user_id = :user_id");
    $check->execute([":child_id" => $childId, ":user_id" => $userId]);
    if (!$check->fetch()) {
        redirectWith("error", "not_found");
    }

    $stmt = $pdo->prepare(
        "UPDATE children
         SET first_name = :first_name,
             last_name = :last_name,
             date_of_birth = :date_of_birth,
             gender = :gender,
             development_domain = :development_domain,
             notes = :notes
         WHERE child_id = :child_id AND user_id = :user_id"
    );
    $stmt->execute([
        ":first_name" => $firstName,
        ":last_name" => $lastName,
        ":date_of_birth" => $dateOfBirth,
        ":gender" => $gender === "" ? null : $gender,
        ":development_domain" => $domain === "" ? null : $domain,
        ":notes" => $notes === "" ? null : $notes,
        ":child_id" => $childId,
        ":user_id" => $userId,
    ]);

    redirectWith("status", "updated");
} catch (PDOException $e) {
    error_log($e->getMessage());
    redirectWith("error", "server");
}
