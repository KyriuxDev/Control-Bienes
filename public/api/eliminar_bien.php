<?php
// public/api/eliminar_bien.php
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (ob_get_level()) ob_end_clean();
ob_start();

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Infrastructure\Repository\MySQLDetalleMovimientoRepository;
use App\Application\UseCase\Bien\DeleteBienUseCase;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Validar ID
    if (empty($_POST['id_bien'])) {
        throw new Exception("El ID del bien es obligatorio");
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $bienRepo = new MySQLBienRepository($pdo);
    $detalleRepo = new MySQLDetalleMovimientoRepository($pdo);
    
    $useCase = new DeleteBienUseCase($bienRepo, $detalleRepo);
    
    $idBien = intval($_POST['id_bien']);
    
    // Ejecutar caso de uso (ya valida si tiene movimientos asociados)
    $resultado = $useCase->execute($idBien);
    
    ob_clean();
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Bien eliminado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo eliminar el bien'
        ]);
    }

} catch (Exception $e) {
    ob_clean();
    error_log("ERROR en eliminar_bien.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;