<?php
// Update a schedule's name, date and description, but only if it belongs to
// a child owned by the logged-in user.
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../front_end/index.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../front_end/schedule.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";

function redirectWith(string $key, string $value): void {
    header("Location: ../../front_end/schedule.php?" . $key . "=" . urlencode($value));
    exit();
}

$scheduleId = filter_input(INPUT_POST, "schedule_id", FILTER_VALIDATE_INT);
$name = trim((string) ($_POST["schedule_name"] ?? ""));
$date = trim((string) ($_POST["schedule_date"] ?? ""));
$description = trim((string) ($_POST["description"] ?? ""));

if (!$scheduleId || $name === "" || $date === "") {
    redirectWith("error", "required");
}
if (strlen($name) > 150) {
    redirectWith("error", "too_long");
}
$parsedDate = DateTime::createFromFormat("Y-m-d", $date);
if (!$parsedDate || $parsedDate->format("Y-m-d") !== $date) {
    redirectWith("error", "date");
}

try {
    $check = $pdo->prepare(
        "SELECT s.schedule_id
         FROM schedules s
         INNER JOIN children c ON c.child_id = s.child_id
         WHERE s.schedule_id = :schedule_id AND c.user_id = :user_id"
    );
    $check->execute([":schedule_id" => $scheduleId, ":user_id" => (int) $_SESSION["user_id"]]);
    if (!$check->fetch()) {
        redirectWith("error", "not_found");
    }

    $stmt = $pdo->prepare(
        "UPDATE schedules
         SET schedule_name = :schedule_name,
             schedule_date = :schedule_date,
             description = :description
         WHERE schedule_id = :schedule_id"
    );
    $stmt->execute([
        ":schedule_name" => $name,
        ":schedule_date" => $date,
        ":description" => $description === "" ? null : $description,
        ":schedule_id" => $scheduleId,
    ]);

    redirectWith("status", "updated");
} catch (PDOException $e) {
    error_log($e->getMessage());
    redirectWith("error", "server");
}
