<?php
// delete.php - delete a task
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

if ($task_id === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Task ID is required"]);
    exit();
}

try {
    // only delete if the task belongs to this user's child
    $sql = "DELETE t FROM tasks t
            JOIN children c ON c.child_id = t.child_id
            WHERE t.task_id = :task_id AND c.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":task_id" => $task_id, ":user_id" => $user_id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Task not found"]);
        exit();
    }

    echo json_encode(["success" => true, "message" => "Task deleted"]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
}
?>
