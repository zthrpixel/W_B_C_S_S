<?php
// Loads the logged-in user's schedules into $schedules, and each schedule's
// activities into $itemsBySchedule[schedule_id].
// Include this file from a page that has already passed auth_check.php.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    exit();
}

require_once __DIR__ . "/../config/database.php";

$schedulesStmt = $pdo->prepare(
    "SELECT s.schedule_id, s.child_id, s.schedule_name, s.schedule_date, s.description,
            c.first_name, c.last_name
     FROM schedules s
     INNER JOIN children c ON c.child_id = s.child_id
     WHERE c.user_id = :user_id
     ORDER BY s.schedule_date DESC, s.schedule_id DESC"
);
$schedulesStmt->execute([":user_id" => (int) $_SESSION["user_id"]]);
$schedules = $schedulesStmt->fetchAll();

$itemsBySchedule = [];
if ($schedules) {
    $scheduleIds = array_map("intval", array_column($schedules, "schedule_id"));
    $placeholders = implode(",", array_fill(0, count($scheduleIds), "?"));

    $itemsStmt = $pdo->prepare(
        "SELECT si.schedule_id, si.start_time, si.end_time, a.activity_name
         FROM schedule_items si
         INNER JOIN activities a ON a.activity_id = si.activity_id
         WHERE si.schedule_id IN ($placeholders)
         ORDER BY si.start_time ASC, si.item_order ASC"
    );
    $itemsStmt->execute($scheduleIds);

    foreach ($itemsStmt->fetchAll() as $item) {
        $itemsBySchedule[$item["schedule_id"]][] = $item;
    }
}
