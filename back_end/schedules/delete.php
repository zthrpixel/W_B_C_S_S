<?php
// Delete a schedule (its items go with it), but only if it belongs to a child
// owned by the logged-in user. Uses POST so a link can never delete data.
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
if (!$scheduleId) {
    redirectWith("error", "required");
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

    $stmt = $pdo->prepare("DELETE FROM schedules WHERE schedule_id = :schedule_id");
    $stmt->execute([":schedule_id" => $scheduleId]);

    redirectWith("status", "deleted");
} catch (PDOException $e) {
    error_log($e->getMessage());
    redirectWith("error", "server");
}
