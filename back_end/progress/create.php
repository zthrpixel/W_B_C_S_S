<?php
// Create or update a progress summary for an owned child.
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit();
}

require_once __DIR__ . "/../config/database.php";

try {
    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";
    $input = [];
    if (strpos($contentType, "application/json") !== false) {
        $input = json_decode(file_get_contents("php://input"), true) ?? [];
    } else {
        $input = $_POST;
    }

    $childId = filter_var($input["child_id"] ?? null, FILTER_VALIDATE_INT);
    $date = trim((string) ($input["progress_date"] ?? ($input["date"] ?? "")));
    $remarks = trim((string) ($input["remarks"] ?? ""));

    if (!$childId) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Child ID is required"]);
        exit();
    }
    if ($date === "") {
        $date = date("Y-m-d");
    }
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid progress date"]);
        exit();
    }

    $ownership = $pdo->prepare(
        "SELECT child_id FROM children WHERE child_id = :child_id AND user_id = :user_id"
    );
    $ownership->execute([
        ":child_id" => $childId,
        ":user_id" => (int) $_SESSION["user_id"]
    ]);
    if (!$ownership->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Child not found"]);
        exit();
    }

    $counts = $pdo->prepare(
        "SELECT COUNT(*) AS total_tasks,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_tasks
         FROM tasks
         WHERE child_id = :child_id
           AND (due_date IS NULL OR due_date = :progress_date)"
    );
    $counts->execute([":child_id" => $childId, ":progress_date" => $date]);
    $summary = $counts->fetch();
    $total = (int) $summary["total_tasks"];
    $completed = (int) $summary["completed_tasks"];
    $percentage = $total > 0 ? round(($completed / $total) * 100, 2) : 0.00;

    $save = $pdo->prepare(
        "INSERT INTO progress_records
         (child_id, progress_date, total_tasks, completed_tasks, completion_percentage, remarks)
         VALUES (:child_id, :progress_date, :total_tasks, :completed_tasks, :percentage, :remarks)
         ON DUPLICATE KEY UPDATE
             total_tasks = VALUES(total_tasks),
             completed_tasks = VALUES(completed_tasks),
             completion_percentage = VALUES(completion_percentage),
             remarks = VALUES(remarks)"
    );
    $save->execute([
        ":child_id" => $childId,
        ":progress_date" => $date,
        ":total_tasks" => $total,
        ":completed_tasks" => $completed,
        ":percentage" => $percentage,
        ":remarks" => $remarks === "" ? null : $remarks
    ]);

    // Fetch the actual record ID because ON DUPLICATE KEY UPDATE may not create a new ID.
    $record = $pdo->prepare(
        "SELECT progress_id
         FROM progress_records
         WHERE child_id = :child_id AND progress_date = :progress_date"
    );
    $record->execute([
        ":child_id" => $childId,
        ":progress_date" => $date
    ]);
    $progressId = (int) $record->fetchColumn();
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Progress recorded",
        "progress_id" => $progressId,
        "total_tasks" => $total,
        "completed_tasks" => $completed,
        "completion_percentage" => $percentage
    ]);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not record progress"]);
}
