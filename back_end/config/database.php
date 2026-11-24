<?php
// Database connection settings. Change these to match your environment.
$dbHost = "localhost";
$dbName = "visual_schedule_system";
$dbUser = "root";
$dbPass = "";

// Keeps PHP and MySQL in agreement about what "today" is, so schedules,
// tasks and progress all line up. Change to your own timezone if needed
// (list: https://www.php.net/manual/en/timezones.php).
$appTimezone = "Asia/Manila";
date_default_timezone_set($appTimezone);

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Make CURDATE() / NOW() in SQL use the same timezone as PHP.
    $utcOffset = (new DateTime("now", new DateTimeZone($appTimezone)))->format("P");
    $pdo->exec("SET time_zone = '" . $utcOffset . "'");
} catch (PDOException $e) {
    // Log the real reason; never show connection details to the browser.
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);

    // JSON endpoints set their Content-Type before loading this file.
    $isJson = false;
    foreach (headers_list() as $header) {
        if (stripos($header, "Content-Type: application/json") === 0) {
            $isJson = true;
            break;
        }
    }

    if ($isJson) {
        echo json_encode(["success" => false, "message" => "Database connection failed"]);
    } else {
        echo "Database connection failed. Please check back_end/config/database.php.";
    }
    exit();
}

// Don't leave database credentials sitting in the global scope of every page.
unset($dbHost, $dbName, $dbUser, $dbPass, $utcOffset);
