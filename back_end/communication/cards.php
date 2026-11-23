<?php
// Read picture cards from an owned communication category.
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
    $categoryId = filter_input(INPUT_GET, "category_id", FILTER_VALIDATE_INT);
    if (!$categoryId) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Category ID is required"]);
        exit();
    }

    $ownership = $pdo->prepare(
        "SELECT category_id FROM communication_categories WHERE category_id = :category_id"
    );
    $ownership->execute([":category_id" => $categoryId]);
    if (!$ownership->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Category not found"]);
        exit();
    }

    $stmt = $pdo->prepare(
        "SELECT card_id, category_id, card_name, image_path, spoken_text, created_at
         FROM communication_cards
         WHERE category_id = :category_id
         ORDER BY card_name ASC"
    );
    $stmt->execute([":category_id" => $categoryId]);

    echo json_encode([
        "success" => true,
        "cards" => $stmt->fetchAll()
    ]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not read cards"]);
}
