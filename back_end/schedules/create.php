<?php
// Create a schedule (and its activities) for a child owned by the logged-in user.
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

$userId = (int) $_SESSION["user_id"];
$childId = filter_input(INPUT_POST, "child_id", FILTER_VALIDATE_INT);
$name = trim((string) ($_POST["schedule_name"] ?? ""));
$date = trim((string) ($_POST["schedule_date"] ?? ""));
$description = trim((string) ($_POST["description"] ?? ""));

$activityTimes = is_array($_POST["activity_time"] ?? null) ? $_POST["activity_time"] : [];
$activityNames = is_array($_POST["activity_name"] ?? null) ? $_POST["activity_name"] : [];

// Validate the schedule itself
if (!$childId || $name === "" || $date === "") {
    redirectWith("error", "required");
}
if (strlen($name) > 150) {
    redirectWith("error", "too_long");
}
$parsedDate = DateTime::createFromFormat("Y-m-d", $date);
if (!$parsedDate || $parsedDate->format("Y-m-d") !== $date) {
    redirectWith("error", "date");
}

// Validate the activity rows: blank rows are skipped, half-filled rows are errors
$activities = [];
foreach ($activityNames as $index => $activityName) {
    $activityName = trim((string) $activityName);
    $time = trim((string) ($activityTimes[$index] ?? ""));

    if ($activityName === "" && $time === "") {
        continue;
    }
    if ($activityName === "" || $time === "" || strlen($activityName) > 150
        || !preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $time)) {
        redirectWith("error", "activity");
    }
    $activities[] = ["name" => $activityName, "time" => $time];
}

try {
    // Confirm the child belongs to the logged-in user
    $check = $pdo->prepare("SELECT child_id FROM children WHERE child_id = :child_id AND user_id = :user_id");
    $check->execute([":child_id" => $childId, ":user_id" => $userId]);
    if (!$check->fetch()) {
        redirectWith("error", "not_found");
    }

    $pdo->beginTransaction();

    $insertSchedule = $pdo->prepare(
        "INSERT INTO schedules (child_id, created_by, schedule_name, schedule_date, description)
         VALUES (:child_id, :created_by, :schedule_name, :schedule_date, :description)"
    );
    $insertSchedule->execute([
        ":child_id" => $childId,
        ":created_by" => $userId,
        ":schedule_name" => $name,
        ":schedule_date" => $date,
        ":description" => $description === "" ? null : $description,
    ]);
    $scheduleId = (int) $pdo->lastInsertId();

    $findActivity = $pdo->prepare(
        "SELECT activity_id FROM activities WHERE activity_name = :name AND created_by = :user_id LIMIT 1"
    );
    $insertActivity = $pdo->prepare(
        "INSERT INTO activities (activity_name, created_by) VALUES (:name, :user_id)"
    );
    $insertItem = $pdo->prepare(
        "INSERT INTO schedule_items (schedule_id, activity_id, start_time, item_order)
         VALUES (:schedule_id, :activity_id, :start_time, :item_order)"
    );

    foreach ($activities as $position => $activity) {
        // Re-use the user's activity of the same name, otherwise create it
        $findActivity->execute([":name" => $activity["name"], ":user_id" => $userId]);
        $activityId = $findActivity->fetchColumn();
        if (!$activityId) {
            $insertActivity->execute([":name" => $activity["name"], ":user_id" => $userId]);
            $activityId = (int) $pdo->lastInsertId();
        }

        $insertItem->execute([
            ":schedule_id" => $scheduleId,
            ":activity_id" => (int) $activityId,
            ":start_time" => $activity["time"],
            ":item_order" => $position + 1,
        ]);
    }

    $pdo->commit();
    redirectWith("status", "created");
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    redirectWith("error", "server");
}
