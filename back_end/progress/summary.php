<?php
// Summarize task progress for a child or all owned children.
session_start();
header("Content-Type: application/json");

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
    $childId = filter_input(INPUT_GET, "child_id", FILTER_VALIDATE_INT);
    $date = trim((string) ($_GET["date"] ?? ($_GET["progress_date"] ?? "")));
    if ($date === "") {
        $date = date("Y-m-d");
    }
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid date"]);
        exit();
    }

    $where = ["c.user_id = :user_id"];
    $params = [":user_id" => $userId];
    if ($childId !== null && $childId !== false) {
        $where[] = "t.child_id = :child_id";
        $params[":child_id"] = $childId;
    }

    $dueClause = "t.due_date = :progress_date";
    if ($date === date("Y-m-d")) {
        $dueClause = "(t.due_date IS NULL OR t.due_date = :progress_date)";
    }
    $sql = "SELECT COUNT(*) AS total_tasks,
                   SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) AS completed_tasks
            FROM tasks t
            INNER JOIN children c ON c.child_id = t.child_id
            WHERE " . implode(" AND ", $where) . "
              AND " . $dueClause;
    $params[":progress_date"] = $date;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $summary = $stmt->fetch();

    $total = (int) $summary["total_tasks"];
    $completed = (int) $summary["completed_tasks"];
    $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

    echo json_encode([
        "success" => true,
        "summary" => [
            "child_id" => $childId ?: null,
            "date" => $date,
            "total_tasks" => $total,
            "completed_tasks" => $completed,
            "pending_tasks" => max(0, $total - $completed),
            "completion_count" => $completed,
            "percentage" => $percentage
        ]
    ]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not read progress summary"]);
}
