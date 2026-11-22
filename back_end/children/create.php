<?php
// create.php - add a new task for a child
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
$child_id = $_POST["child_id"] ?? "";
$title = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$due_date = trim($_POST["due_date"] ?? "");
$priority = $_POST["priority"] ?? "normal";

// check required fields
if ($child_id === "" || $title === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Child and title are required"]);
    exit();
}

if (!in_array($priority, ["low", "normal", "high"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid priority"]);
    exit();
}

try {
    // make sure the child belongs to the logged in user
    $stmt = $pdo->prepare("SELECT child_id FROM children
                           WHERE child_id = :child_id AND user_id = :user_id");
    $stmt->execute([":child_id" => $child_id, ":user_id" => $user_id]);

    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Child not found"]);
        exit();
    }

    // empty description and due date are saved as NULL
    $sql = "INSERT INTO tasks (child_id, assigned_by, title, description, due_date, priority)
            VALUES (:child_id, :assigned_by, :title, :description, :due_date, :priority)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":child_id" => $child_id,
        ":assigned_by" => $user_id,
        ":title" => $title,
        ":description" => $description === "" ? null : $description,
        ":due_date" => $due_date === "" ? null : $due_date,
        ":priority" => $priority
    ]);

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Task added"]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
}
?>
