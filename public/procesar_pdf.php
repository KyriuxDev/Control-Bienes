<?php
// public/procesar_pdf.php - CORREGIDO
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
        if (empty($item['id_bien'])) continue; // Skip empty rows
        
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
    
    // Verificar que haya al menos un bien
    if (empty($bienesSeleccionados)) {
        throw new Exception("Debe seleccionar al menos un bien");
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
    // ✅ CORREGIDO: Ahora están bien asignados
    $datosAdicionales = array(
        'folio' => isset($_POST['folio']) ? $_POST['folio'] : '',
        'lugar_fecha' => $_POST['lugar'] . ', ' . date('d \d\e F \d\e Y', strtotime($_POST['fecha'])),
        'recibe_resguardo' => $trabajadorRecibe->getNombre(),  // ✅ Quien RECIBE
        'entrega_resguardo' => $trabajadorEntrega->getNombre(), // ✅ Quien ENTREGA
        'cargo_entrega' => $trabajadorEntrega->getCargo(),       // ✅ Cargo de quien entrega
        'tipo_documento' => $_POST['tipo_movimiento']
    );
    
    // 5. GENERAR PDF (usando el trabajador que recibe)
    $nombreArchivo = strtolower(str_replace(' ', '_', $_POST['tipo_movimiento'])) . '_' . 
                     preg_replace('/[^a-z0-9_]/', '', strtolower($trabajadorRecibe->getNombre())) . '_' . 
                     time() . '.pdf';
    $rutaSalida = __DIR__ . '/pdfs/' . $nombreArchivo;
    
    // Asegurar que existe el directorio
    if (!file_exists(__DIR__ . '/pdfs')) {
        mkdir(__DIR__ . '/pdfs', 0775, true);
    }
    
    $generador = new GeneradorResguardoPDF();
    $generador->generar($trabajadorRecibe, $bienesSeleccionados, $datosAdicionales, $rutaSalida);
    
    // 6. FORZAR DESCARGA
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Content-Length: ' . filesize($rutaSalida));
    readfile($rutaSalida);
    
    // Borrar temporal después de 5 segundos (permite que se complete la descarga)
    register_shutdown_function(function() use ($rutaSalida) {
        if (file_exists($rutaSalida)) {
            sleep(5);
            @unlink($rutaSalida);
        }
    });
    
} catch (Exception $e) {
    // Rollback en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("ERROR en procesar_pdf.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Mostrar error al usuario
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md">
            <div class="flex items-center gap-3 text-red-600 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h1 class="text-xl font-bold">Error al Generar PDF</h1>
            </div>
            <p class="text-gray-700 mb-4">' . htmlspecialchars($e->getMessage()) . '</p>
            <button onclick="window.history.back()" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                Volver al Formulario
            </button>
        </div>
    </body>
    </html>';
}

exit;