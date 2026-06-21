<?php
/**
 * Bond Simulator API — CUD Operations
 * 
 * The CORE of all data operations for Bond Simulator.
 * All Create, Update, Delete operations go through this PHP file.
 * 
 * Endpoints:
 *   GET  ?action=list              — List all saved simulations
 *   GET  ?action=get&id=X          — Get single simulation
 *   GET  ?action=predict&e1=X&e2=Y — Predict bond type from 2 element symbols
 *   POST action=create             — Create new bond simulation
 *   POST action=update             — Update existing simulation
 *   POST action=delete             — Delete simulation
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

// Electronegativity data (Pauling scale) for bond prediction
$electronegativities = [
    'H' => 2.20, 'He' => 0, 'Li' => 0.98, 'Be' => 1.57, 'B' => 2.04,
    'C' => 2.55, 'N' => 3.04, 'O' => 3.44, 'F' => 3.98, 'Ne' => 0,
    'Na' => 0.93, 'Mg' => 1.31, 'Al' => 1.61, 'Si' => 1.90, 'P' => 2.19,
    'S' => 2.58, 'Cl' => 3.16, 'Ar' => 0, 'K' => 0.82, 'Ca' => 1.00,
    'Sc' => 1.36, 'Ti' => 1.54, 'V' => 1.63, 'Cr' => 1.66, 'Mn' => 1.55,
    'Fe' => 1.83, 'Co' => 1.88, 'Ni' => 1.91, 'Cu' => 1.90, 'Zn' => 1.65,
    'Ga' => 1.81, 'Ge' => 2.01, 'As' => 2.18, 'Se' => 2.55, 'Br' => 2.96,
    'Kr' => 3.00, 'Rb' => 0.82, 'Sr' => 0.95, 'Y' => 1.22, 'Zr' => 1.33,
    'Nb' => 1.60, 'Mo' => 2.16, 'Tc' => 1.90, 'Ru' => 2.20, 'Rh' => 2.28,
    'Pd' => 2.20, 'Ag' => 1.93, 'Cd' => 1.69, 'In' => 1.78, 'Sn' => 1.96,
    'Sb' => 2.05, 'Te' => 2.10, 'I' => 2.66, 'Xe' => 2.60, 'Cs' => 0.79,
    'Ba' => 0.89, 'La' => 1.10, 'Ce' => 1.12, 'Pr' => 1.13, 'Nd' => 1.14,
    'Pm' => 1.13, 'Sm' => 1.17, 'Eu' => 1.20, 'Gd' => 1.20, 'Tb' => 1.10,
    'Dy' => 1.22, 'Ho' => 1.23, 'Er' => 1.24, 'Tm' => 1.25, 'Yb' => 1.10,
    'Lu' => 1.27, 'Hf' => 1.30, 'Ta' => 1.50, 'W' => 2.36, 'Re' => 1.90,
    'Os' => 2.20, 'Ir' => 2.20, 'Pt' => 2.28, 'Au' => 2.54, 'Hg' => 2.00,
    'Tl' => 1.62, 'Pb' => 1.87, 'Bi' => 2.02, 'Po' => 2.00, 'At' => 2.20,
    'Rn' => 2.20, 'Fr' => 0.70, 'Ra' => 0.90, 'Ac' => 1.10, 'Th' => 1.30,
    'Pa' => 1.50, 'U' => 1.38, 'Np' => 1.36, 'Pu' => 1.28, 'Am' => 1.30,
    'Cm' => 1.30, 'Bk' => 1.30, 'Cf' => 1.30, 'Es' => 1.30, 'Fm' => 1.30,
    'Md' => 1.30, 'No' => 1.30, 'Lr' => 1.30, 'Rf' => 0, 'Db' => 0,
    'Sg' => 0, 'Bh' => 0, 'Hs' => 0, 'Mt' => 0, 'Ds' => 0,
    'Rg' => 0, 'Cn' => 0, 'Nh' => 0, 'Fl' => 0, 'Mc' => 0,
    'Lv' => 0, 'Ts' => 0, 'Og' => 0
];

// Metal categories for metallic bond detection
$metalCategories = [
    'Alkali metal', 'Alkaline earth metal', 'Transition metal', 
    'Post-transition metal', 'Lanthanide', 'Actinide'
];

/**
 * Predict bond type based on two elements
 */
function predictBondType($pdo, $symbol1, $symbol2, $electronegativities, $metalCategories) {
    // Fetch element data
    $stmt = $pdo->prepare('SELECT symbol, name, category FROM elements WHERE symbol = ?');
    
    $stmt->execute([$symbol1]);
    $el1 = $stmt->fetch();
    
    $stmt->execute([$symbol2]);
    $el2 = $stmt->fetch();
    
    if (!$el1 || !$el2) {
        return null;
    }
    
    $en1 = $electronegativities[$symbol1] ?? 0;
    $en2 = $electronegativities[$symbol2] ?? 0;
    $diff = abs($en1 - $en2);
    
    $cat1 = strtolower($el1['category']);
    $cat2 = strtolower($el2['category']);
    $isMetal1 = (strpos($cat1, 'metal') !== false) && (strpos($cat1, 'nonmetal') === false) && (strpos($cat1, 'metalloid') === false);
    $isMetal2 = (strpos($cat2, 'metal') !== false) && (strpos($cat2, 'nonmetal') === false) && (strpos($cat2, 'metalloid') === false);
    
    // Determine bond type
    $bondTypeId = 2; // Default: Covalent
    $bondTypeName = 'Covalent';
    
    if ($isMetal1 && $isMetal2) {
        $bondTypeId = 4;
        $bondTypeName = 'Metallic';
    } elseif ($diff > 1.7) {
        $bondTypeId = 1;
        $bondTypeName = 'Ionic';
    } elseif ($diff >= 0.4) {
        $bondTypeId = 3;
        $bondTypeName = 'Polar Covalent';
    } else {
        $bondTypeId = 2;
        $bondTypeName = 'Covalent';
    }
    
    // Get bond type description
    $stmt2 = $pdo->prepare('SELECT * FROM bond_types WHERE id = ?');
    $stmt2->execute([$bondTypeId]);
    $bondType = $stmt2->fetch();
    
    return [
        'element1' => $el1,
        'element2' => $el2,
        'electronegativity1' => $en1,
        'electronegativity2' => $en2,
        'electronegativity_diff' => round($diff, 2),
        'bond_type_id' => $bondTypeId,
        'bond_type_name' => $bondTypeName,
        'bond_type' => $bondType
    ];
}

// --- ROUTE HANDLER ---
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        
        // ============================================
        // READ: List all saved simulations
        // ============================================
        case 'list':
            $stmt = $pdo->query('SELECT * FROM bond_simulations ORDER BY created_at DESC');
            $simulations = $stmt->fetchAll();
            echo json_encode(["status" => "success", "data" => $simulations]);
            break;
        
        // ============================================
        // READ: Get single simulation
        // ============================================
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Invalid ID"]);
                break;
            }
            $stmt = $pdo->prepare('SELECT * FROM bond_simulations WHERE id = ?');
            $stmt->execute([$id]);
            $sim = $stmt->fetch();
            if (!$sim) {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Simulation not found"]);
                break;
            }
            echo json_encode(["status" => "success", "data" => $sim]);
            break;
        
        // ============================================
        // PREDICT: Determine bond type from 2 elements
        // ============================================
        case 'predict':
            $e1 = trim($_GET['e1'] ?? '');
            $e2 = trim($_GET['e2'] ?? '');
            if (empty($e1) || empty($e2)) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Both element symbols (e1, e2) are required"]);
                break;
            }
            $prediction = predictBondType($pdo, $e1, $e2, $electronegativities, $metalCategories);
            if (!$prediction) {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "One or both elements not found"]);
                break;
            }
            echo json_encode(["status" => "success", "data" => $prediction]);
            break;
        
        // ============================================
        // CREATE: Save new bond simulation
        // ============================================
        case 'create':
            $e1_symbol = trim($_POST['element1_symbol'] ?? '');
            $e1_name = trim($_POST['element1_name'] ?? '');
            $e2_symbol = trim($_POST['element2_symbol'] ?? '');
            $e2_name = trim($_POST['element2_name'] ?? '');
            $bond_type_id = intval($_POST['bond_type_id'] ?? 0);
            $bond_type_name = trim($_POST['bond_type_name'] ?? '');
            $bond_count = intval($_POST['bond_count'] ?? 1);
            $en_diff = floatval($_POST['electronegativity_diff'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            
            // Validation
            if (empty($e1_symbol) || empty($e2_symbol)) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Both elements are required"]);
                break;
            }
            if ($bond_count < 1 || $bond_count > 3) {
                $bond_count = 1;
            }
            
            $stmt = $pdo->prepare(
                'INSERT INTO bond_simulations (element1_symbol, element1_name, element2_symbol, element2_name, bond_type_id, bond_type_name, bond_count, electronegativity_diff, notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$e1_symbol, $e1_name, $e2_symbol, $e2_name, $bond_type_id ?: null, $bond_type_name, $bond_count, $en_diff, $notes]);
            
            $newId = $pdo->lastInsertId();
            
            // Fetch the created record
            $stmt2 = $pdo->prepare('SELECT * FROM bond_simulations WHERE id = ?');
            $stmt2->execute([$newId]);
            $created = $stmt2->fetch();
            
            echo json_encode([
                "status" => "success",
                "message" => "Simulation created successfully",
                "data" => $created
            ]);
            break;
        
        // ============================================
        // UPDATE: Edit existing simulation
        // ============================================
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Valid ID is required"]);
                break;
            }
            
            // Check exists
            $check = $pdo->prepare('SELECT id FROM bond_simulations WHERE id = ?');
            $check->execute([$id]);
            if (!$check->fetch()) {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Simulation not found"]);
                break;
            }
            
            $bond_count = intval($_POST['bond_count'] ?? 1);
            $notes = trim($_POST['notes'] ?? '');
            if ($bond_count < 1 || $bond_count > 3) $bond_count = 1;
            
            $stmt = $pdo->prepare(
                'UPDATE bond_simulations SET bond_count = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
            );
            $stmt->execute([$bond_count, $notes, $id]);
            
            // Fetch updated record
            $stmt2 = $pdo->prepare('SELECT * FROM bond_simulations WHERE id = ?');
            $stmt2->execute([$id]);
            $updated = $stmt2->fetch();
            
            echo json_encode([
                "status" => "success",
                "message" => "Simulation updated successfully",
                "data" => $updated
            ]);
            break;
        
        // ============================================
        // DELETE: Remove simulation
        // ============================================
        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Valid ID is required"]);
                break;
            }
            
            // Check exists
            $check = $pdo->prepare('SELECT id FROM bond_simulations WHERE id = ?');
            $check->execute([$id]);
            if (!$check->fetch()) {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Simulation not found"]);
                break;
            }
            
            $stmt = $pdo->prepare('DELETE FROM bond_simulations WHERE id = ?');
            $stmt->execute([$id]);
            
            echo json_encode([
                "status" => "success",
                "message" => "Simulation deleted successfully"
            ]);
            break;
        
        default:
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid action. Valid actions: list, get, predict, create, update, delete"
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}
?>
