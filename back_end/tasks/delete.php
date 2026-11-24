<?php
// Delete a task only when it belongs to a child owned by the logged-in user.
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
    if (!$taskId) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Task ID is required"]);
        exit();
    }

    $check = $pdo->prepare(
        "SELECT t.task_id
         FROM tasks t
         INNER JOIN children c ON c.child_id = t.child_id
         WHERE t.task_id = :task_id AND c.user_id = :user_id"
    );
    $check->execute([":task_id" => $taskId, ":user_id" => $_SESSION["user_id"]]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Task not found"]);
        exit();
    }

    $delete = $pdo->prepare("DELETE FROM tasks WHERE task_id = :task_id");
    $delete->execute([":task_id" => $taskId]);
    echo json_encode(["success" => true, "message" => "Task deleted"]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not delete task"]);
}
