<?php
// =============================================
// Chemiverse API — Reactions (CUD + Read)
// =============================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    // ============================================
    // LIST: All reactions (with optional type filter)
    // ============================================
    case 'list':
        $type = trim($_GET['type'] ?? '');
        
        if (!empty($type)) {
            $stmt = $pdo->prepare("SELECT id, name, reactants, products, equation, type, energy, reversible, conditions, description FROM reactions WHERE type = ? ORDER BY name");
            $stmt->execute([$type]);
        } else {
            $stmt = $pdo->query("SELECT id, name, reactants, products, equation, type, energy, reversible, conditions, description FROM reactions ORDER BY type, name");
        }
        
        $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Decode JSON fields
        foreach ($reactions as &$r) {
            $r['reactants'] = json_decode($r['reactants'], true);
            $r['products'] = json_decode($r['products'], true);
        }
        echo json_encode(["status" => "success", "data" => $reactions]);
        break;
    
    // ============================================
    // GET: Single reaction by ID
    // ============================================
    case 'get':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Valid reaction ID is required"]);
            break;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM reactions WHERE id = ?");
        $stmt->execute([$id]);
        $reaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reaction) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Reaction not found"]);
            break;
        }
        
        $reaction['reactants'] = json_decode($reaction['reactants'], true);
        $reaction['products'] = json_decode($reaction['products'], true);
        
        echo json_encode(["status" => "success", "data" => $reaction]);
        break;
    
    // ============================================
    // PREDICT: Find reaction by reactants
    // ============================================
    case 'predict':
        $r1 = trim($_GET['r1'] ?? '');
        $r2 = trim($_GET['r2'] ?? '');
        
        if (empty($r1)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "At least one reactant (r1) is required"]);
            break;
        }
        
        // Search for reactions containing the provided reactants
        $stmt = $pdo->query("SELECT * FROM reactions ORDER BY id");
        $allReactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $matches = [];
        foreach ($allReactions as $rx) {
            $reactants = json_decode($rx['reactants'], true);
            $reactantsLower = array_map('strtolower', $reactants);
            
            $r1Lower = strtolower($r1);
            $r2Lower = strtolower($r2);
            
            // Check if provided reactants match this reaction
            if (!empty($r2)) {
                // Two reactants provided — both must be in the reaction
                if (in_array($r1Lower, $reactantsLower) && in_array($r2Lower, $reactantsLower)) {
                    $rx['reactants'] = $reactants;
                    $rx['products'] = json_decode($rx['products'], true);
                    $matches[] = $rx;
                }
            } else {
                // One reactant — must be the only reactant (decomposition)
                if (count($reactants) === 1 && in_array($r1Lower, $reactantsLower)) {
                    $rx['reactants'] = $reactants;
                    $rx['products'] = json_decode($rx['products'], true);
                    $matches[] = $rx;
                }
            }
        }
        
        if (count($matches) > 0) {
            echo json_encode(["status" => "success", "data" => $matches]);
        } else {
            echo json_encode(["status" => "not_found", "message" => "Tidak ada reaksi yang ditemukan untuk reaktan tersebut", "data" => []]);
        }
        break;
    
    // ============================================
    // TYPES: List distinct reaction types
    // ============================================
    case 'types':
        $stmt = $pdo->query("SELECT type, COUNT(*) as count FROM reactions GROUP BY type ORDER BY type");
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $types]);
        break;
    
    // ============================================
    // SAVE: Save a new experiment (CUD - Create)
    // ============================================
    case 'save':
        $reactionId = intval($_POST['reaction_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        
        if ($reactionId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Valid reaction_id is required"]);
            break;
        }
        
        // Verify reaction exists
        $check = $pdo->prepare("SELECT id FROM reactions WHERE id = ?");
        $check->execute([$reactionId]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Reaction not found"]);
            break;
        }
        
        $stmt = $pdo->prepare("INSERT INTO reaction_experiments (reaction_id, notes) VALUES (?, ?)");
        $stmt->execute([$reactionId, $notes]);
        
        echo json_encode(["status" => "success", "message" => "Eksperimen berhasil disimpan", "id" => $pdo->lastInsertId()]);
        break;
    
    // ============================================
    // DELETE: Delete an experiment (CUD - Delete)
    // ============================================
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Valid experiment ID is required"]);
            break;
        }
        
        $stmt = $pdo->prepare("DELETE FROM reaction_experiments WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Eksperimen berhasil dihapus"]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Experiment not found"]);
        }
        break;
    
    // ============================================
    // EXPERIMENTS: List saved experiments
    // ============================================
    case 'experiments':
        $stmt = $pdo->query("
            SELECT e.id, e.reaction_id, e.notes, e.created_at,
                   r.name as reaction_name, r.equation, r.type
            FROM reaction_experiments e
            JOIN reactions r ON e.reaction_id = r.id
            ORDER BY e.created_at DESC
        ");
        $experiments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $experiments]);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid action. Use: list, get, predict, types, save, delete, experiments"]);
        break;
}
