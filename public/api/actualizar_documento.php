<?php
// public/api/actualizar_documento.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

if (ob_get_level()) ob_end_clean();
ob_start();

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLMovimientoRepository;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLDetalleMovimientoRepository;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Application\UseCase\Movimiento\UpdateMovimientoUseCase;
use App\Application\UseCase\DetalleMovimiento\UpdateDetalleMovimientoUseCase;
use App\Application\UseCase\DetalleMovimiento\CreateDetalleMovimientoUseCase;
use App\Application\DTO\MovimientoDTO;
use App\Application\DTO\DetalleMovimientoDTO;

header('Content-Type: application/json; charset=utf-8');

error_log("=== ACTUALIZAR DOCUMENTO API ===");
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Validar ID del movimiento
    if (!isset($_POST['id_movimiento']) || $_POST['id_movimiento'] === '' || $_POST['id_movimiento'] === null) {
        throw new Exception("El ID del documento es obligatorio para actualizar");
    }

    $idMovimiento = intval($_POST['id_movimiento']);
    
    if ($idMovimiento <= 0) {
        throw new Exception("El ID del documento no es válido: " . $_POST['id_movimiento']);
    }
    
    error_log("Actualizando documento con ID: $idMovimiento");

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $movimientoRepo = new MySQLMovimientoRepository($pdo);
    $trabajadorRepo = new MySQLTrabajadorRepository($pdo);
    $detalleRepo = new MySQLDetalleMovimientoRepository($pdo);
    $bienRepo = new MySQLBienRepository($pdo);
    
    // Verificar que el movimiento existe
    $movimientoExistente = $movimientoRepo->obtenerPorId($idMovimiento);
    if (!$movimientoExistente) {
        throw new Exception("No se encontró el documento con ID: $idMovimiento");
    }
    
    error_log("Documento encontrado: " . $movimientoExistente->getFolio());
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        // 1. ACTUALIZAR MOVIMIENTO (si vienen campos para actualizar)
        $movimientoActualizado = false;
        
        if (isset($_POST['actualizar_movimiento']) && $_POST['actualizar_movimiento'] === '1') {
            error_log("Actualizando datos del movimiento...");
            
            // Validar trabajadores si vienen
            if (isset($_POST['matricula_recibe']) && !empty($_POST['matricula_recibe'])) {
                $trabajadorRecibe = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_recibe']);
                if (!$trabajadorRecibe) {
                    throw new Exception("Trabajador que recibe no encontrado");
                }
            }
            
            if (isset($_POST['matricula_entrega']) && !empty($_POST['matricula_entrega'])) {
                $trabajadorEntrega = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_entrega']);
                if (!$trabajadorEntrega) {
                    throw new Exception("Trabajador que entrega no encontrado");
                }
            }
            
            // Crear DTO con los datos a actualizar
            $movimientoDTO = new MovimientoDTO([
                'id_movimiento' => $idMovimiento,
                'tipo_movimiento' => isset($_POST['tipo_movimiento']) ? $_POST['tipo_movimiento'] : $movimientoExistente->getTipoMovimiento(),
                'matricula_recibe' => isset($_POST['matricula_recibe']) ? $_POST['matricula_recibe'] : $movimientoExistente->getMatriculaRecibe(),
                'matricula_entrega' => isset($_POST['matricula_entrega']) ? $_POST['matricula_entrega'] : $movimientoExistente->getMatriculaEntrega(),
                'fecha' => isset($_POST['fecha']) ? $_POST['fecha'] : $movimientoExistente->getFecha(),
                'lugar' => isset($_POST['lugar']) ? $_POST['lugar'] : $movimientoExistente->getLugar(),
                'area' => isset($_POST['area']) ? $_POST['area'] : $movimientoExistente->getArea(),
                'folio' => $movimientoExistente->getFolio(), // El folio NO se modifica
                'dias_prestamo' => isset($_POST['dias_prestamo']) ? intval($_POST['dias_prestamo']) : $movimientoExistente->getDiasPrestamo()
            ]);
            
            $updateMovimientoUseCase = new UpdateMovimientoUseCase($movimientoRepo, $trabajadorRepo);
            $updateMovimientoUseCase->execute($movimientoDTO);
            
            error_log("Movimiento actualizado exitosamente");
            $movimientoActualizado = true;
        }
        
        // 2. ACTUALIZAR DETALLES (si vienen bienes para actualizar)
        $detallesActualizados = false;
        
        if (isset($_POST['bienes']) && is_array($_POST['bienes']) && !empty($_POST['bienes'])) {
            error_log("Actualizando detalles de bienes...");
            
            // Obtener estado global
            $estadoGeneral = isset($_POST['estado_general']) ? $_POST['estado_general'] : 'Buenas condiciones';
            
            if (isset($_POST['estado_general']) && $_POST['estado_general'] === 'Otro' && isset($_POST['estado_otro']) && !empty($_POST['estado_otro'])) {
                $estadoGeneral = $_POST['estado_otro'];
            }
            
            $sujetoDevolucionGlobal = isset($_POST['sujeto_devolucion_global']) ? intval($_POST['sujeto_devolucion_global']) : 0;
            
            // Eliminar todos los detalles existentes
            $detallesExistentes = $detalleRepo->buscarPorMovimiento($idMovimiento);
            foreach ($detallesExistentes as $detalle) {
                $detalleRepo->eliminarDetalle($idMovimiento, $detalle->getIdBien());
            }
            
            error_log("Detalles anteriores eliminados");
            
            // Crear nuevos detalles
            $createDetalleUseCase = new CreateDetalleMovimientoUseCase($detalleRepo, $movimientoRepo, $bienRepo);
            
            foreach ($_POST['bienes'] as $item) {
                if (empty($item['id_bien'])) continue;
                
                // Verificar que el bien existe
                $bien = $bienRepo->obtenerPorId($item['id_bien']);
                if (!$bien) {
                    error_log("ADVERTENCIA: Bien no encontrado con ID: " . $item['id_bien']);
                    continue;
                }
                
                $detalleDTO = new DetalleMovimientoDTO([
                    'id_movimiento' => $idMovimiento,
                    'id_bien' => $item['id_bien'],
                    'cantidad' => isset($item['cantidad']) ? intval($item['cantidad']) : 1,
                    'estado_fisico' => $estadoGeneral,
                    'sujeto_devolucion' => $sujetoDevolucionGlobal
                ]);
                
                $createDetalleUseCase->execute($detalleDTO);
            }
            
            error_log("Nuevos detalles creados");
            $detallesActualizados = true;
        }
        
        // Commit de la transacción
        $pdo->commit();
        error_log("Transacción completada exitosamente");
        
        // Preparar respuesta
        $mensaje = [];
        if ($movimientoActualizado) $mensaje[] = 'información del documento';
        if ($detallesActualizados) $mensaje[] = 'bienes asociados';
        
        $mensajeFinal = 'Documento actualizado correctamente';
        if (!empty($mensaje)) {
            $mensajeFinal .= ' (' . implode(' y ', $mensaje) . ')';
        }
        
        ob_clean();
        
        echo json_encode([
            'success' => true,
            'message' => $mensajeFinal,
            'documento' => [
                'id' => $idMovimiento,
                'folio' => $movimientoExistente->getFolio()
            ]
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    ob_clean();
    error_log("ERROR en actualizar_documento.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;