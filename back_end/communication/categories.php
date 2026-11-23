<?php
// Read picture-card categories owned by the logged-in caregiver.
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
    $stmt = $pdo->prepare(
        "SELECT category_id, category_name, description
         FROM communication_categories
         ORDER BY category_name ASC"
    );
    $stmt->execute();

    $categories = $stmt->fetchAll();
    echo json_encode([
        "success" => true,
        "data" => $categories,
        "categories" => $categories
    ]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not read categories"]);
}
