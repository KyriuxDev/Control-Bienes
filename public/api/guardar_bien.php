<?php
// public/api/guardar_bien.php
error_reporting(E_ALL);
ini_set('display_errors', 0); // NO mostrar errores en pantalla, solo en logs

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Application\UseCase\Bien\CreateBienUseCase;
use App\Application\DTO\BienDTO;

// IMPORTANTE: Limpiar cualquier salida previa
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Validar que llegue la descripción
    if (empty($_POST['descripcion'])) {
        throw new Exception("La descripción es obligatoria");
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $bienRepo = new MySQLBienRepository($pdo);
    
    $useCase = new CreateBienUseCase($bienRepo);
    
    $dto = new BienDTO([
        'descripcion' => trim($_POST['descripcion']),
        'identificacion' => !empty($_POST['identificacion']) 
                           ? trim($_POST['identificacion']) 
                           : 'AUTO-' . time(),
        'naturaleza' => !empty($_POST['naturaleza']) 
                       ? $_POST['naturaleza'] 
                       : 'BMNC',
        'marca' => isset($_POST['marca']) ? trim($_POST['marca']) : '',
        'modelo' => isset($_POST['modelo']) ? trim($_POST['modelo']) : '',
        'serie' => isset($_POST['serie']) ? trim($_POST['serie']) : '',
        'estado_fisico' => !empty($_POST['estado_fisico']) 
                          ? $_POST['estado_fisico'] 
                          : 'BUENO'
    ]);
    
    $resultado = $useCase->execute($dto);

} catch (Exception $e) {
    error_log("ERROR en guardar_bien.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;