<?php
// public/api/guardar_bien.php - VERSIÓN CORREGIDA SOLO PARA CREAR
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

// Limpiar buffer
if (ob_get_level()) ob_end_clean();
ob_start();

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Application\UseCase\Bien\CreateBienUseCase;
use App\Application\DTO\BienDTO;

header('Content-Type: application/json; charset=utf-8');

// Log de inicio
error_log("=== GUARDAR BIEN API (CREAR NUEVO) ===");
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // VALIDACIÓN: Si viene un ID, rechazar - este endpoint es SOLO para crear
    if (isset($_POST['id_bien']) && $_POST['id_bien'] !== '' && $_POST['id_bien'] !== null) {
        error_log("ERROR: Se intentó usar guardar_bien.php para actualizar (ID: " . $_POST['id_bien'] . ")");
        throw new Exception("Este endpoint es solo para crear nuevos bienes. Use actualizar_bien.php para modificar.");
    }

    // Validar que llegue la descripción
    if (empty($_POST['descripcion']) || trim($_POST['descripcion']) === '') {
        throw new Exception("La descripción es obligatoria");
    }

    error_log("Creando nuevo bien: " . $_POST['descripcion']);

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $bienRepo = new MySQLBienRepository($pdo);
    
    $useCase = new CreateBienUseCase($bienRepo);
    
    // Crear DTO - SIN ID (será asignado automáticamente)
    $dto = new BienDTO([
        'descripcion' => trim($_POST['descripcion']),
        'naturaleza' => !empty($_POST['naturaleza']) ? $_POST['naturaleza'] : 'BMNC',
        'marca' => isset($_POST['marca']) ? trim($_POST['marca']) : '',
        'modelo' => isset($_POST['modelo']) ? trim($_POST['modelo']) : '',
        'serie' => isset($_POST['serie']) ? trim($_POST['serie']) : ''
    ]);
    
    error_log("DTO creado (sin ID): " . print_r($dto->toArray(), true));
    
    $resultado = $useCase->execute($dto);
    
    error_log("Bien creado exitosamente con ID: " . $resultado->id_bien);
    
    // Limpiar buffer antes de enviar JSON
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'message' => 'Bien guardado correctamente',
        'bien' => [
            'id' => $resultado->id_bien,
            'descripcion' => $resultado->descripcion,
            'marca' => $resultado->marca,
            'modelo' => $resultado->modelo,
            'serie' => $resultado->serie,
            'naturaleza' => $resultado->naturaleza
        ]
    ]);

} catch (Exception $e) {
    ob_clean();
    error_log("ERROR en guardar_bien.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;