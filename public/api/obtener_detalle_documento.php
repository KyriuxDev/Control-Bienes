<?php
// public/api/obtener_detalle_documento.php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLMovimientoRepository;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLDetalleMovimientoRepository;
use App\Infrastructure\Repository\MySQLBienRepository;

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("ID de documento no proporcionado");
    }
    
    $idMovimiento = intval($_GET['id']);
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $movimientoRepo = new MySQLMovimientoRepository($pdo);
    $trabajadorRepo = new MySQLTrabajadorRepository($pdo);
    $detalleRepo = new MySQLDetalleMovimientoRepository($pdo);
    $bienRepo = new MySQLBienRepository($pdo);
    
    // Obtener el movimiento
    $movimiento = $movimientoRepo->obtenerPorId($idMovimiento);
    
    if (!$movimiento) {
        throw new Exception("Documento no encontrado");
    }
    
    // Obtener trabajadores
    $trabajadorRecibe = $trabajadorRepo->obtenerPorMatricula($movimiento->getMatriculaRecibe());
    $trabajadorEntrega = $trabajadorRepo->obtenerPorMatricula($movimiento->getMatriculaEntrega());
    
    if (!$trabajadorRecibe || !$trabajadorEntrega) {
        throw new Exception("Error al cargar información de trabajadores");
    }
    
    // Obtener detalles del movimiento con información de bienes
    $detalles = $detalleRepo->buscarPorMovimiento($idMovimiento);
    
    $detallesConBienes = array();
    foreach ($detalles as $detalle) {
        $bien = $bienRepo->obtenerPorId($detalle->getIdBien());
        
        if ($bien) {
            $detallesConBienes[] = array(
                'id_movimiento' => $detalle->getIdMovimiento(),
                'id_bien' => $detalle->getIdBien(),
                'cantidad' => $detalle->getCantidad(),
                'estado_fisico' => $detalle->getEstadoFisico(),
                'sujeto_devolucion' => $detalle->getSujetoDevolucion(),
                'bien' => array(
                    'id_bien' => $bien->getIdBien(),
                    'descripcion' => $bien->getDescripcion(),
                    'naturaleza' => $bien->getNaturaleza(),
                    'marca' => $bien->getMarca(),
                    'modelo' => $bien->getModelo(),
                    'serie' => $bien->getSerie()
                )
            );
        }
    }
    
    echo json_encode([
        'success' => true,
        'movimiento' => array(
            'id_movimiento' => $movimiento->getIdMovimiento(),
            'folio' => $movimiento->getFolio(),
            'tipo_movimiento' => $movimiento->getTipoMovimiento(),
            'fecha' => $movimiento->getFecha(),
            'lugar' => $movimiento->getLugar(),
            'area' => $movimiento->getArea(),
            'dias_prestamo' => $movimiento->getDiasPrestamo(),
            'fecha_devolucion' => $movimiento->getFechaDevolucion() // NUEVO
        ),
        'trabajadores' => array(
            'recibe' => array(
                'matricula' => $trabajadorRecibe->getMatricula(),
                'nombre' => $trabajadorRecibe->getNombre(),
                'cargo' => $trabajadorRecibe->getCargo(),
                'institucion' => $trabajadorRecibe->getInstitucion(),
                'adscripcion' => $trabajadorRecibe->getAdscripcion()
            ),
            'entrega' => array(
                'matricula' => $trabajadorEntrega->getMatricula(),
                'nombre' => $trabajadorEntrega->getNombre(),
                'cargo' => $trabajadorEntrega->getCargo(),
                'institucion' => $trabajadorEntrega->getInstitucion(),
                'adscripcion' => $trabajadorEntrega->getAdscripcion()
            )
        ),
        'detalles' => $detallesConBienes
    ]);
    
} catch (Exception $e) {
    error_log("ERROR en obtener_detalle_documento.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;