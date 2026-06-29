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

    // ============================================
    // LIST_CUSTOM: All user-built custom molecules
    // ============================================
    case 'list_custom':
        $stmt = $pdo->query("SELECT id, name, formula, category, description, molecular_weight, created_at FROM molecules WHERE is_custom = 1 ORDER BY created_at DESC");
        $molecules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $molecules]);
        break;

    // ============================================
    // SAVE: Create a new custom molecule (POST)
    // ============================================
    case 'save':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "POST method required"]);
            break;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['name']) || empty($input['atoms'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "name and atoms are required"]);
            break;
        }

        $name = trim($input['name']);
        $atoms = $input['atoms']; // [{el, x, y, z}, ...]
        $bonds = $input['bonds'] ?? []; // [{a, b, order}, ...]

        // Auto-calculate formula from atom composition
        $elementCounts = [];
        foreach ($atoms as $atom) {
            $el = $atom['el'];
            $elementCounts[$el] = ($elementCounts[$el] ?? 0) + 1;
        }
        // Hill system ordering: C first, H second, then alphabetical
        uksort($elementCounts, function($a, $b) {
            if ($a === 'C') return -1;
            if ($b === 'C') return 1;
            if ($a === 'H') return -1;
            if ($b === 'H') return 1;
            return strcmp($a, $b);
        });
        $formula = '';
        foreach ($elementCounts as $el => $count) {
            $formula .= $el . ($count > 1 ? $count : '');
        }

        // Auto-calculate molecular weight
        $atomicWeights = [
            'H'=>1.008,'He'=>4.003,'Li'=>6.941,'Be'=>9.012,'B'=>10.81,'C'=>12.011,
            'N'=>14.007,'O'=>15.999,'F'=>18.998,'Ne'=>20.180,'Na'=>22.990,'Mg'=>24.305,
            'Al'=>26.982,'Si'=>28.086,'P'=>30.974,'S'=>32.065,'Cl'=>35.453,'Ar'=>39.948,
            'K'=>39.098,'Ca'=>40.078,'Fe'=>55.845,'Br'=>79.904,'I'=>126.904,'Zn'=>65.38
        ];
        $mw = 0;
        foreach ($atoms as $atom) {
            $mw += $atomicWeights[$atom['el']] ?? 0;
        }

        $structureData = json_encode(['atoms' => $atoms, 'bonds' => $bonds]);

        $stmt = $pdo->prepare("INSERT INTO molecules (name, formula, category, description, molecular_weight, structure_data, is_custom) VALUES (?, ?, 'custom', ?, ?, ?, 1)");
        $stmt->execute([
            $name,
            $formula,
            $input['description'] ?? 'Custom molecule built in Sandbox mode',
            round($mw, 3),
            $structureData
        ]);

        $newId = $pdo->lastInsertId();
        echo json_encode(["status" => "success", "id" => $newId, "formula" => $formula, "molecular_weight" => round($mw, 3)]);
        break;

    // ============================================
    // UPDATE: Update a custom molecule (POST)
    // ============================================
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "POST method required"]);
            break;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Valid molecule ID required"]);
            break;
        }

        // Only allow updating custom molecules
        $check = $pdo->prepare("SELECT id FROM molecules WHERE id = ? AND is_custom = 1");
        $check->execute([$id]);
        if (!$check->fetch()) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Can only update custom molecules"]);
            break;
        }

        $updates = [];
        $params = [];

        if (!empty($input['name'])) {
            $updates[] = "name = ?";
            $params[] = trim($input['name']);
        }
        if (isset($input['atoms'])) {
            $atoms = $input['atoms'];
            $bonds = $input['bonds'] ?? [];
            $structureData = json_encode(['atoms' => $atoms, 'bonds' => $bonds]);
            $updates[] = "structure_data = ?";
            $params[] = $structureData;

            // Recalculate formula and weight
            $elementCounts = [];
            foreach ($atoms as $atom) {
                $el = $atom['el'];
                $elementCounts[$el] = ($elementCounts[$el] ?? 0) + 1;
            }
            uksort($elementCounts, function($a, $b) {
                if ($a === 'C') return -1;
                if ($b === 'C') return 1;
                if ($a === 'H') return -1;
                if ($b === 'H') return 1;
                return strcmp($a, $b);
            });
            $formula = '';
            foreach ($elementCounts as $el => $count) {
                $formula .= $el . ($count > 1 ? $count : '');
            }
            $updates[] = "formula = ?";
            $params[] = $formula;

            $atomicWeights = [
                'H'=>1.008,'He'=>4.003,'C'=>12.011,'N'=>14.007,'O'=>15.999,
                'F'=>18.998,'Na'=>22.990,'P'=>30.974,'S'=>32.065,'Cl'=>35.453,
                'K'=>39.098,'Ca'=>40.078,'Fe'=>55.845,'Br'=>79.904,'I'=>126.904
            ];
            $mw = 0;
            foreach ($atoms as $atom) {
                $mw += $atomicWeights[$atom['el']] ?? 0;
            }
            $updates[] = "molecular_weight = ?";
            $params[] = round($mw, 3);
        }

        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "No fields to update"]);
            break;
        }

        $params[] = $id;
        $sql = "UPDATE molecules SET " . implode(", ", $updates) . " WHERE id = ?";
        $pdo->prepare($sql)->execute($params);

        echo json_encode(["status" => "success", "message" => "Molecule updated"]);
        break;

    // ============================================
    // DELETE: Remove a custom molecule (POST)
    // ============================================
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "POST method required"]);
            break;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Valid molecule ID required"]);
            break;
        }

        // Only allow deleting custom molecules
        $stmt = $pdo->prepare("DELETE FROM molecules WHERE id = ? AND is_custom = 1");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Custom molecule not found"]);
            break;
        }

        echo json_encode(["status" => "success", "message" => "Molecule deleted"]);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid action. Use: list, get, categories, list_custom, save, update, delete"]);
        break;
}
