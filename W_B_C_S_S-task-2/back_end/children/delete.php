<?php
// Delete a child profile (and, through foreign keys, their tasks, schedules
// and progress), but only if it belongs to the logged-in user.
// Uses POST so a link or a browser prefetch can never delete data.
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
if (!$childId) {
    redirectWith("error", "required");
}

try {
    $stmt = $pdo->prepare("DELETE FROM children WHERE child_id = :child_id AND user_id = :user_id");
    $stmt->execute([":child_id" => $childId, ":user_id" => (int) $_SESSION["user_id"]]);

    if ($stmt->rowCount() === 0) {
        redirectWith("error", "not_found");
    }
    redirectWith("status", "deleted");
} catch (PDOException $e) {
    error_log($e->getMessage());
    redirectWith("error", "server");
}
