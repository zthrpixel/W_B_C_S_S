<?php
// Create a picture card in a category owned by the logged-in caregiver.
session_start();
header("Content-Type: application/json");

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
    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";
    $input = [];
    if (strpos($contentType, "application/json") !== false) {
        $input = json_decode(file_get_contents("php://input"), true) ?? [];
    } else {
        $input = $_POST;
    }

    $categoryId = filter_var($input["category_id"] ?? null, FILTER_VALIDATE_INT);
    $label = trim((string) ($input["card_name"] ?? $input["label"] ?? ""));
    $imagePath = trim((string) ($input["image_path"] ?? ""));
    $spokenText = trim((string) ($input["spoken_text"] ?? ""));

    if (!$categoryId || $label === "") {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Category and card label are required"]);
        exit();
    }
    if (strlen($label) > 100) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Card label must be 100 characters or less"]);
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
        "INSERT INTO communication_cards
         (category_id, card_name, image_path, spoken_text)
         VALUES (:category_id, :card_name, :image_path, :spoken_text)"
    );
    $stmt->execute([
        ":category_id" => $categoryId,
        ":card_name" => $label,
        ":image_path" => $imagePath === "" ? null : $imagePath,
        ":spoken_text" => $spokenText === "" ? null : $spokenText
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Picture card created",
        "card_id" => (int) $pdo->lastInsertId()
    ]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not create picture card"]);
}
