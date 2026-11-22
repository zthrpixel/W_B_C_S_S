<?php
// update.php - edit a task
// a task can't be set to completed here, use complete.php for that
session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "POST only"]);
    exit();
}

$user_id = $_SESSION["user_id"];
$task_id = $_POST["task_id"] ?? "";
$title = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$due_date = trim($_POST["due_date"] ?? "");
$priority = $_POST["priority"] ?? "normal";
$status = $_POST["status"] ?? "pending";

if ($task_id === "" || $title === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Title is required"]);
    exit();
}

if (!in_array($priority, ["low", "normal", "high"]) ||
    !in_array($status, ["pending", "in_progress", "completed"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid priority or status"]);
    exit();
}

try {
    // get the task, only if it belongs to this user's child
    $stmt = $pdo->prepare("SELECT t.status FROM tasks t
                           JOIN children c ON c.child_id = t.child_id
                           WHERE t.task_id = :task_id AND c.user_id = :user_id");
    $stmt->execute([":task_id" => $task_id, ":user_id" => $user_id]);
    $task = $stmt->fetch();

    if (!$task) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Task not found"]);
        exit();
    }

    // can't complete a task here
    if ($status === "completed" && $task["status"] !== "completed") {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Use the Complete button instead"]);
        exit();
    }

    $sql = "UPDATE tasks SET title = :title, description = :description,
            due_date = :due_date, priority = :priority, status = :status
            WHERE task_id = :task_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":title" => $title,
        ":description" => $description === "" ? null : $description,
        ":due_date" => $due_date === "" ? null : $due_date,
        ":priority" => $priority,
        ":status" => $status,
        ":task_id" => $task_id
    ]);

    // if a completed task is set back to pending/in progress,
    // remove its completion record so the progress stays correct
    if ($task["status"] === "completed" && $status !== "completed") {
        $stmt = $pdo->prepare("DELETE FROM task_completions WHERE task_id = :task_id");
        $stmt->execute([":task_id" => $task_id]);
    }

    echo json_encode(["success" => true, "message" => "Task updated"]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
}
?>
