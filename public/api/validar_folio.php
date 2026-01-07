<?php
// public/api/validar_folio.php
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (ob_get_level()) ob_end_clean();
ob_start();

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    if (empty($_POST['folio'])) {
        throw new Exception("El folio es obligatorio");
    }

    $folio = trim($_POST['folio']);
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Verificar si el folio ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM movimiento WHERE folio = :folio");
    $stmt->execute(['folio' => $folio]);
    $result = $stmt->fetch();
    
    ob_clean();
    
    if ($result['total'] > 0) {
        echo json_encode([
            'success' => false,
            'existe' => true,
            'message' => 'Este folio ya está registrado en el sistema'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'existe' => false,
            'message' => 'Folio disponible'
        ]);
    }

} catch (Exception $e) {
    ob_clean();
    error_log("ERROR en validar_folio.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;