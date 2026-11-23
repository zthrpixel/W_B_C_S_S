<?php
// Read stored progress records for children owned by the logged-in user.
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit();
}

require_once __DIR__ . "/../config/database.php";

try {
    $userId = (int) $_SESSION["user_id"];
    $where = ["c.user_id = :user_id"];
    $params = [":user_id" => $userId];

    $childId = filter_input(INPUT_GET, "child_id", FILTER_VALIDATE_INT);
    if ($childId !== null && $childId !== false) {
        $where[] = "t.child_id = :child_id";
        $params[":child_id"] = $childId;
    }

    $date = trim((string) ($_GET["date"] ?? ($_GET["progress_date"] ?? "")));
    if ($date !== "") {
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid date"]);
            exit();
        }
        $where[] = "pr.progress_date = :progress_date";
        $params[":progress_date"] = $date;
    }

    $sql = "SELECT pr.progress_id, pr.child_id, pr.progress_date,
                   pr.total_tasks, pr.completed_tasks, pr.completion_percentage,
                   pr.remarks, pr.created_at,
                   c.first_name AS child_first_name, c.last_name AS child_last_name
            FROM progress_records pr
            INNER JOIN children c ON c.child_id = pr.child_id
            WHERE " . implode(" AND ", $where) . "
            ORDER BY pr.progress_date DESC, pr.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        "success" => true,
        "progress" => $stmt->fetchAll()
    ]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not read progress"]);
}
