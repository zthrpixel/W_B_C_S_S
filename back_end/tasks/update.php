<?php
// Edit an owned task; completed status is only changed by complete.php.
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
    $title = trim((string) ($_POST["title"] ?? ""));
    $description = trim((string) ($_POST["description"] ?? ""));
    $dueDate = trim((string) ($_POST["due_date"] ?? ""));
    $priority = trim((string) ($_POST["priority"] ?? ""));
    $status = trim((string) ($_POST["status"] ?? ""));
    $allowedPriorities = ["low", "normal", "high"];
    $allowedStatuses = ["pending", "in_progress"];

    if (!$taskId) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Task ID is required"]);
        exit();
    }
    if ($title === "" || strlen($title) > 150) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Title is required and must be 150 characters or less"]);
        exit();
    }
    if (!in_array($priority, $allowedPriorities, true)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid priority"]);
        exit();
    }
    if (!in_array($status, $allowedStatuses, true)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Completed tasks must be reopened with this endpoint"]);
        exit();
    }
    if ($dueDate !== "" && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $dueDate)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid due date"]);
        exit();
    }

    // Fetch the task only if it belongs to a child owned by the current user.
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
    $update = $pdo->prepare(
        "UPDATE tasks
         SET title = :title, description = :description, due_date = :due_date,
             priority = :priority, status = :status
         WHERE task_id = :task_id"
    );
    $update->execute([
        ":title" => $title,
        ":description" => $description === "" ? null : $description,
        ":due_date" => $dueDate === "" ? null : $dueDate,
        ":priority" => $priority,
        ":status" => $status,
        ":task_id" => $taskId
    ]);

    // Reopening a completed task removes its completion history.
    if ($task["status"] === "completed" && $status !== "completed") {
        $deleteCompletions = $pdo->prepare("DELETE FROM task_completions WHERE task_id = :task_id");
        $deleteCompletions->execute([":task_id" => $taskId]);
    }

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Task updated"]);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not update task"]);
}
