<?php
// Delete a picture card from the shared library.
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

    $cardId = filter_var($input["card_id"] ?? null, FILTER_VALIDATE_INT);
    if (!$cardId || $cardId < 1) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "A valid card ID is required."
        ]);
        exit();
    }

    $delete = $pdo->prepare("DELETE FROM communication_cards WHERE card_id = ?");
    $delete->execute([$cardId]);

    if ($delete->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Communication card not found."
        ]);
        exit();
    }

    echo json_encode([
        "success" => true,
        "message" => "Communication card deleted successfully."
    ]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to delete communication card."
    ]);
}
