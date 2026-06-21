<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

try {
    $stmt = $pdo->query('SELECT * FROM bond_types ORDER BY id');
    $types = $stmt->fetchAll();
    
    echo json_encode([
        "status" => "success",
        "data" => $types
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch bond types: " . $e->getMessage()
    ]);
}
?>
