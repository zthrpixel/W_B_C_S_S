<?php
// read.php - get the tasks of the logged in user's children
session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/../config/database.php';

// check if user is logged in
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION["user_id"];

// only get tasks of children that belong to this user
$sql = "SELECT t.task_id, t.child_id, t.title, t.description, t.due_date,
               t.priority, t.status,
               CONCAT(c.first_name, ' ', c.last_name) AS child_name
        FROM tasks t
        JOIN children c ON c.child_id = t.child_id
        WHERE c.user_id = :user_id";
$params = [":user_id" => $user_id];

// filter by child
if (!empty($_GET["child_id"])) {
    $sql .= " AND t.child_id = :child_id";
    $params[":child_id"] = $_GET["child_id"];
}

// filter by status
if (!empty($_GET["status"])) {
    $sql .= " AND t.status = :status";
    $params[":status"] = $_GET["status"];
}

// for the child view: no due date, due today, or late and not done yet
if (isset($_GET["today"])) {
    $sql .= " AND (t.due_date IS NULL OR t.due_date = CURDATE()
              OR (t.due_date < CURDATE() AND t.status != 'completed'))";
}

// unfinished tasks first
$sql .= " ORDER BY t.status = 'completed', t.task_id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(["success" => true, "tasks" => $stmt->fetchAll()]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
}
?>
