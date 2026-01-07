<?php
// public/procesar_pdf.php - ACTUALIZADO PARA NUEVA BASE DE DATOS
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/generadores/GeneradorResguardoPDF.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Infrastructure\Repository\MySQLMovimientoRepository;
use App\Infrastructure\Repository\MySQLDetalleMovimientoRepository;
use App\Domain\Entity\Movimiento;
use App\Domain\Entity\Detalle_Movimiento;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$db = Database::getInstance();
$pdo = $db->getConnection();

$trabajadorRepo = new MySQLTrabajadorRepository($pdo);
$bienRepo = new MySQLBienRepository($pdo);
$movimientoRepo = new MySQLMovimientoRepository($pdo);
$detalleRepo = new MySQLDetalleMovimientoRepository($pdo);

try {
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // 1. CREAR MOVIMIENTO (CABECERA)
    $movimiento = new Movimiento();
    $movimiento->setTipoMovimiento($_POST['tipo_movimiento'])
               ->setMatriculaRecibe($_POST['matricula_recibe'])
               ->setMatriculaEntrega($_POST['matricula_entrega'])
               ->setFecha($_POST['fecha'])
               ->setLugar(isset($_POST['lugar']) ? $_POST['lugar'] : '')
               ->setArea(isset($_POST['area']) ? $_POST['area'] : '')
               ->setFolio(isset($_POST['folio']) ? $_POST['folio'] : '')
               ->setDiasPrestamo(isset($_POST['dias_prestamo']) ? $_POST['dias_prestamo'] : null);
    
    $movimientoRepo->persist($movimiento);
    $idMovimiento = $movimiento->getIdMovimiento();
    
    // 2. CREAR DETALLES (BIENES)
    $bienesSeleccionados = array();
    foreach ($_POST['bienes'] as $item) {
        $bienObj = $bienRepo->obtenerPorId($item['id_bien']);
        if ($bienObj) {
            // Guardar detalle en BD
            $detalle = new Detalle_Movimiento();
            $detalle->setIdMovimiento($idMovimiento)
                   ->setIdBien($item['id_bien'])
                   ->setCantidad(isset($item['cantidad']) ? $item['cantidad'] : 1)
                   ->setEstadoFisico(isset($item['estado_fisico']) ? $item['estado_fisico'] : '')
                   ->setSujetoDevolucion(isset($item['sujeto_devolucion']) ? 1 : 0);
            
            $detalleRepo->persist($detalle);
            
            // Agregar a array para el PDF
            $bienesSeleccionados[] = array(
                'bien' => $bienObj,
                'cantidad' => isset($item['cantidad']) ? $item['cantidad'] : 1
            );
        }
    }
    
    // Commit de la transacción
    $pdo->commit();
    
    // 3. OBTENER TRABAJADORES PARA EL PDF
    $trabajadorRecibe = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_recibe']);
    $trabajadorEntrega = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_entrega']);
    
    if (!$trabajadorRecibe || !$trabajadorEntrega) {
        throw new Exception("Error: Trabajadores no encontrados");
    }
    
    // 4. PREPARAR DATOS ADICIONALES PARA EL PDF
    $datosAdicionales = array(
        'folio' => isset($_POST['folio']) ? $_POST['folio'] : '',
        'lugar_fecha' => $_POST['lugar'] . ', ' . date('d \d\e F \d\e Y', strtotime($_POST['fecha'])),
        'recibe_resguardo' => $trabajadorEntrega->getNombre(), // Quien entrega
        'entrega_resguardo' => $trabajadorEntrega->getCargo(), // Su cargo
        'tipo_documento' => $_POST['tipo_movimiento']
    );
    
    // 5. GENERAR PDF (usando el trabajador que recibe)
    $rutaSalida = __DIR__ . '/pdfs/' . strtolower(str_replace(' ', '_', $_POST['tipo_movimiento'])) . '_' . time() . '.pdf';
    
    // Asegurar que existe el directorio
    if (!file_exists(__DIR__ . '/pdfs')) {
        mkdir(__DIR__ . '/pdfs', 0775, true);
    }
    
    $generador = new GeneradorResguardoPDF();
    $generador->generar($trabajadorRecibe, $bienesSeleccionados, $datosAdicionales, $rutaSalida);
    
    // 6. FORZAR DESCARGA
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($rutaSalida) . '"');
    readfile($rutaSalida);
    unlink($rutaSalida); // Borrar temporal
    
} catch (Exception $e) {
    // Rollback en caso de error
    $pdo->rollBack();
    error_log("ERROR en procesar_pdf.php: " . $e->getMessage());
    die("Error al procesar el documento: " . $e->getMessage());
}

exit;