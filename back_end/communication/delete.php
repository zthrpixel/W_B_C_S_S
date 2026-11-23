<?php
// Delete an owned picture card.
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
    $cardId = filter_input(INPUT_POST, "card_id", FILTER_VALIDATE_INT);
    if (!$cardId) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Card ID is required"]);
        exit();
    }

    $check = $pdo->prepare("SELECT card_id FROM communication_cards WHERE card_id = :card_id");
    $check->execute([":card_id" => $cardId]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Picture card not found"]);
        exit();
    }

    $delete = $pdo->prepare("DELETE FROM communication_cards WHERE card_id = :card_id");
    $delete->execute([":card_id" => $cardId]);

    echo json_encode(["success" => true, "message" => "Picture card deleted"]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not delete picture card"]);
}
