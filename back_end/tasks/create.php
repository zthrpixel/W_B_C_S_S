<?php
// Create a task for a child owned by the logged-in user.
session_start();
header("Content-Type: application/json");

// Backend requests return JSON instead of redirecting when authentication fails.
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
    $childId = filter_input(INPUT_POST, "child_id", FILTER_VALIDATE_INT);
    $title = trim((string) ($_POST["title"] ?? ""));
    $description = trim((string) ($_POST["description"] ?? ""));
    $dueDate = trim((string) ($_POST["due_date"] ?? ""));
    $priority = trim((string) ($_POST["priority"] ?? "normal"));
    $allowedPriorities = ["low", "normal", "high"];

    // Required and enum values are checked before executing a database insert.
    if (!$childId || $childId < 1 || $title === "" || $title === null) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Child and title are required"]);
        exit();
    }
    if (strlen($title) > 150) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Title must be 150 characters or less"]);
        exit();
    }
    if (!in_array($priority, $allowedPriorities, true)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid priority"]);
        exit();
    }
    if ($dueDate !== "" && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $dueDate)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid due date"]);
        exit();
    }

    // Confirm ownership so no user can assign a task to another user's child.
    $ownership = $pdo->prepare("SELECT child_id FROM children WHERE child_id = :child_id AND user_id = :user_id");
    $ownership->execute([
        ":child_id" => $childId,
        ":user_id" => $_SESSION["user_id"]
    ]);
    if (!$ownership->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Child not found"]);
        exit();
    }

    $stmt = $pdo->prepare(
        "INSERT INTO tasks (child_id, assigned_by, title, description, due_date, priority, status)
         VALUES (:child_id, :assigned_by, :title, :description, :due_date, :priority, 'pending')"
    );
    $stmt->execute([
        ":child_id" => $childId,
        ":assigned_by" => $_SESSION["user_id"],
        ":title" => $title,
        ":description" => $description === "" ? null : $description,
        ":due_date" => $dueDate === "" ? null : $dueDate,
        ":priority" => $priority
    ]);

    $taskId = (int) $pdo->lastInsertId();
    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Task created", "task_id" => $taskId]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not create task"]);
}
