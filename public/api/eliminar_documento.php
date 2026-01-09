<?php
// public/api/eliminar_documento.php
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (ob_get_level()) ob_end_clean();
ob_start();

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLMovimientoRepository;
use App\Infrastructure\Repository\MySQLDetalleMovimientoRepository;
use App\Application\UseCase\Movimiento\DeleteMovimientoUseCase;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Validar ID
    if (empty($_POST['id_movimiento'])) {
        throw new Exception("El ID del documento es obligatorio");
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $movimientoRepo = new MySQLMovimientoRepository($pdo);
    $detalleRepo = new MySQLDetalleMovimientoRepository($pdo);
    
    $idMovimiento = intval($_POST['id_movimiento']);
    
    // Verificar que el movimiento existe
    $movimiento = $movimientoRepo->obtenerPorId($idMovimiento);
    if (!$movimiento) {
        throw new Exception("Documento no encontrado");
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        // Primero eliminar los detalles asociados
        $detalles = $detalleRepo->buscarPorMovimiento($idMovimiento);
        if (!empty($detalles)) {
            foreach ($detalles as $detalle) {
                $detalleRepo->eliminarDetalle($idMovimiento, $detalle->getIdBien());
            }
        }
        
        // Luego eliminar el movimiento
        $resultado = $movimientoRepo->eliminar($idMovimiento);
        
        if ($resultado) {
            $pdo->commit();
            
            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Documento eliminado correctamente'
            ]);
        } else {
            throw new Exception("No se pudo eliminar el documento");
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    ob_clean();
    error_log("ERROR en eliminar_documento.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;