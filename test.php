<?php
require_once 'api/db.php';
try {
    $stmt = $pdo->query('SELECT * FROM elements');
    $elements = $stmt->fetchAll();
    echo "Success! Row count: " . count($elements);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
