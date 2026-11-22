<?php
// complete.php - mark a task as done and save it in task_completions
// the progress page (Task 4) uses the task_completions table
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
$remarks = trim($_POST["remarks"] ?? "");

if ($task_id === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Task ID is required"]);
    exit();
}

try {
    // check that the task belongs to this user's child
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

    // already done, so don't add another completion record
    if ($task["status"] === "completed") {
        echo json_encode(["success" => true, "message" => "Task is already completed"]);
        exit();
    }

    // do both queries together so one can't succeed without the other
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE tasks SET status = 'completed' WHERE task_id = :task_id");
    $stmt->execute([":task_id" => $task_id]);

    $stmt = $pdo->prepare("INSERT INTO task_completions (task_id, completed_by, completion_date, remarks)
                           VALUES (:task_id, :user_id, CURDATE(), :remarks)");
    $stmt->execute([
        ":task_id" => $task_id,
        ":user_id" => $user_id,
        ":remarks" => $remarks === "" ? null : $remarks
    ]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Task completed"]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
}
?>
