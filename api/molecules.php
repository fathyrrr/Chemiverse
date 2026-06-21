<?php
// =============================================
// Chemiverse API — Molecules (Read-Only)
// =============================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? 'list';

switch ($action) {
    // ============================================
    // LIST: All molecules (summary, no structure)
    // ============================================
    case 'list':
        $category = trim($_GET['category'] ?? '');
        
        if (!empty($category)) {
            $stmt = $pdo->prepare("SELECT id, name, formula, category, description, molecular_weight, created_at FROM molecules WHERE category = ? ORDER BY name");
            $stmt->execute([$category]);
        } else {
            $stmt = $pdo->query("SELECT id, name, formula, category, description, molecular_weight, created_at FROM molecules ORDER BY category, name");
        }
        
        $molecules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $molecules]);
        break;
    
    // ============================================
    // GET: Single molecule with full structure data
    // ============================================
    case 'get':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Valid molecule ID is required"]);
            break;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM molecules WHERE id = ?");
        $stmt->execute([$id]);
        $molecule = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$molecule) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Molecule not found"]);
            break;
        }
        
        // Decode structure_data from JSON string to object
        $molecule['structure_data'] = json_decode($molecule['structure_data'], true);
        
        echo json_encode(["status" => "success", "data" => $molecule]);
        break;
    
    // ============================================
    // CATEGORIES: List distinct categories
    // ============================================
    case 'categories':
        $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM molecules GROUP BY category ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $categories]);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid action. Use: list, get, categories"]);
        break;
}
