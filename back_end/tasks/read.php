<?php
// Read owned tasks, owned children, and today's schedule data.
session_start();
header("Content-Type: application/json");

// Backend requests return JSON 401 instead of performing an auth redirect.
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit();
}

require_once __DIR__ . "/../config/database.php";

try {
    $userId = (int) $_SESSION["user_id"];

    // A small children action powers both front-end child dropdowns.
    if (($_GET["action"] ?? "") === "children") {
        $stmt = $pdo->prepare(
            "SELECT child_id, first_name, last_name
             FROM children
             WHERE user_id = :user_id
             ORDER BY first_name, last_name"
        );
        $stmt->execute([":user_id" => $userId]);
        echo json_encode(["success" => true, "children" => $stmt->fetchAll()]);
        exit();
    }

    $where = ["c.user_id = :user_id"];
    $params = [":user_id" => $userId];

    $childId = filter_input(INPUT_GET, "child_id", FILTER_VALIDATE_INT);
    if ($childId !== null && $childId !== false) {
        $where[] = "t.child_id = :child_id";
        $params[":child_id"] = $childId;
    }

    $status = trim((string) ($_GET["status"] ?? ""));
    $allowedStatuses = ["pending", "in_progress", "completed"];
    if ($status !== "" && in_array($status, $allowedStatuses, true)) {
        $where[] = "t.status = :status";
        $params[":status"] = $status;
    }

    if (($_GET["scope"] ?? "") === "today") {
        $where[] = "(t.due_date IS NULL OR t.due_date = CURDATE())";
    }

    $sql = "SELECT t.task_id, t.child_id, t.assigned_by, t.title, t.description,
                   t.due_date, t.priority, t.status, t.created_at, t.updated_at,
                   c.first_name AS child_first_name, c.last_name AS child_last_name
            FROM tasks t
            INNER JOIN children c ON c.child_id = t.child_id
            WHERE " . implode(" AND ", $where) . "
            ORDER BY
                CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END,
                CASE t.priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END,
                t.due_date IS NULL,
                t.due_date ASC,
                t.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
    $response = ["success" => true, "tasks" => $tasks];

    // The child view can retrieve today's schedule in the same isolated request.
    if (($_GET["include_schedule"] ?? "") === "1" && $childId) {
        $scheduleStmt = $pdo->prepare(
            "SELECT s.schedule_id, s.schedule_name, s.description AS schedule_description,
                    si.item_id, si.start_time, si.end_time, si.item_order, si.reminder_enabled,
                    a.activity_id, a.activity_name, a.description AS activity_description,
                    a.icon, a.category, a.default_duration
             FROM schedules s
             LEFT JOIN schedule_items si ON si.schedule_id = s.schedule_id
             LEFT JOIN activities a ON a.activity_id = si.activity_id
             INNER JOIN children c ON c.child_id = s.child_id
             WHERE s.child_id = :child_id
               AND c.user_id = :user_id
               AND s.schedule_date = CURDATE()
             ORDER BY si.start_time ASC, si.item_order ASC"
        );
        $scheduleStmt->execute([
            ":child_id" => $childId,
            ":user_id" => $userId
        ]);
        $response["schedule"] = $scheduleStmt->fetchAll();
    }

    echo json_encode($response);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not read tasks"]);
}
