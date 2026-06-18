<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow local testing

require_once 'db.php';

try {
    $stmt = $pdo->query('SELECT * FROM elements');
    $elements = $stmt->fetchAll();
    
    // Return structured JSON
    echo json_encode([
        "status" => "success",
        "data" => $elements
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch elements"
    ]);
}
?>
