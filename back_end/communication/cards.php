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
    $cards = [];

    if ($categoryId !== null && $categoryId !== false) {
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
    } else {
        $stmt = $pdo->prepare(
            "SELECT card_id, category_id, card_name, image_path, spoken_text, created_at
             FROM communication_cards
             ORDER BY card_name ASC"
        );
        $stmt->execute();
    }
    $cards = $stmt->fetchAll();

    echo json_encode([
        "success" => true,
        "data" => $cards,
        "cards" => $cards
    ]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not read cards"]);
}
