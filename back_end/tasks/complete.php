<?php
// Complete an owned task atomically and create at most one completion row.
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
    $taskId = filter_input(INPUT_POST, "task_id", FILTER_VALIDATE_INT);
    $remarks = trim((string) ($_POST["remarks"] ?? ""));
    if (!$taskId) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Task ID is required"]);
        exit();
    }

    $check = $pdo->prepare(
        "SELECT t.task_id, t.status
         FROM tasks t
         INNER JOIN children c ON c.child_id = t.child_id
         WHERE t.task_id = :task_id AND c.user_id = :user_id"
    );
    $check->execute([":task_id" => $taskId, ":user_id" => $_SESSION["user_id"]]);
    $task = $check->fetch();
    if (!$task) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Task not found"]);
        exit();
    }

    $pdo->beginTransaction();
    $update = $pdo->prepare("UPDATE tasks SET status = 'completed' WHERE task_id = :task_id");
    $update->execute([":task_id" => $taskId]);

    // Repeated clicks and requests do not create duplicate completion rows.
    $completion = $pdo->prepare(
        "SELECT completion_id
         FROM task_completions
         WHERE task_id = :task_id
         LIMIT 1"
    );
    $completion->execute([":task_id" => $taskId]);
    if (!$completion->fetch()) {
        $insert = $pdo->prepare(
            "INSERT INTO task_completions
             (task_id, completed_by, completion_date, remarks)
             VALUES (:task_id, :completed_by, CURDATE(), :remarks)"
        );
        $insert->execute([
            ":task_id" => $taskId,
            ":completed_by" => $_SESSION["user_id"],
            ":remarks" => $remarks === "" ? null : $remarks
        ]);
    }

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Task completed"]);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not complete task"]);
}
