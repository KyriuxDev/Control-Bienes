<?php
// public/procesar_pdf.php - VERSIÓN MÚLTIPLES FORMATOS CON FOLIO AUTOMÁTICO
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/generadores/GeneradorResguardoPDF.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Infrastructure\Repository\MySQLMovimientoRepository;
use App\Infrastructure\Repository\MySQLDetalleMovimientoRepository;
use App\Infrastructure\Helper\FolioGenerator;
use App\Domain\Entity\Movimiento;
use App\Domain\Entity\Detalle_Movimiento;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$db = Database::getInstance();
$pdo = $db->getConnection();

$trabajadorRepo = new MySQLTrabajadorRepository($pdo);
$bienRepo = new MySQLBienRepository($pdo);
$movimientoRepo = new MySQLMovimientoRepository($pdo);
$detalleRepo = new MySQLDetalleMovimientoRepository($pdo);
$folioGenerator = new FolioGenerator($pdo);

try {
    // Validar que haya tipos de movimiento seleccionados
    if (!isset($_POST['tipos_movimiento']) || empty($_POST['tipos_movimiento'])) {
        throw new Exception("Debe seleccionar al menos un tipo de documento");
    }
    
    // GENERAR FOLIO AUTOMÁTICAMENTE
    $folio = $folioGenerator->generarFolioUnico();
    
    $tiposMovimiento = $_POST['tipos_movimiento'];
    $archivosGenerados = array();
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Obtener trabajadores
    $trabajadorRecibe = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_recibe']);
    $trabajadorEntrega = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_entrega']);
    
    if (!$trabajadorRecibe || !$trabajadorEntrega) {
        throw new Exception("Error: Trabajadores no encontrados");
    }
    
    // Preparar bienes
    $bienesSeleccionados = array();
    foreach ($_POST['bienes'] as $item) {
        if (empty($item['id_bien'])) continue;
        
        $bienObj = $bienRepo->obtenerPorId($item['id_bien']);
        if ($bienObj) {
            $bienesSeleccionados[] = array(
                'bien' => $bienObj,
                'cantidad' => isset($item['cantidad']) ? $item['cantidad'] : 1
            );
        }
    }
    
    if (empty($bienesSeleccionados)) {
        throw new Exception("Debe seleccionar al menos un bien");
    }
    
    // Generar cada tipo de documento seleccionado
    foreach ($tiposMovimiento as $tipoMovimiento) {
        // 1. Crear movimiento para este tipo
        $movimiento = new Movimiento();
        $movimiento->setTipoMovimiento($tipoMovimiento)
                   ->setMatriculaRecibe($_POST['matricula_recibe'])
                   ->setMatriculaEntrega($_POST['matricula_entrega'])
                   ->setFecha($_POST['fecha'])
                   ->setLugar(isset($_POST['lugar']) ? $_POST['lugar'] : '')
                   ->setArea(isset($_POST['area']) ? $_POST['area'] : '')
                   ->setFolio($folio)
                   ->setDiasPrestamo(isset($_POST['dias_prestamo']) ? $_POST['dias_prestamo'] : null);
        
        $movimientoRepo->persist($movimiento);
        $idMovimiento = $movimiento->getIdMovimiento();
        
        // 2. Crear detalles para este movimiento
        foreach ($_POST['bienes'] as $item) {
            if (empty($item['id_bien'])) continue;
            
            $detalle = new Detalle_Movimiento();
            $detalle->setIdMovimiento($idMovimiento)
                   ->setIdBien($item['id_bien'])
                   ->setCantidad(isset($item['cantidad']) ? $item['cantidad'] : 1)
                   ->setEstadoFisico(isset($item['estado_fisico']) ? $item['estado_fisico'] : '')
                   ->setSujetoDevolucion(isset($item['sujeto_devolucion']) ? 1 : 0);
            
            $detalleRepo->persist($detalle);
        }
        
        // 3. Preparar datos para el PDF
        // Corregir problema de zona horaria
        $fechaSeleccionada = $_POST['fecha'];
        $timestamp = strtotime($fechaSeleccionada . ' 12:00:00'); // Agregar hora del mediodía para evitar problemas de zona horaria
        
        $datosAdicionales = array(
            'folio' => $folio,
            'lugar_fecha' => $_POST['lugar'] . ', ' . date('d \d\e F \d\e Y', $timestamp),
            'recibe_resguardo' => $trabajadorRecibe->getNombre(),
            'entrega_resguardo' => $trabajadorEntrega->getNombre(),
            'cargo_entrega' => $trabajadorEntrega->getCargo(),
            'tipo_documento' => $tipoMovimiento
        );
        
        // 4. Generar PDF
        $nombreArchivo = strtolower(str_replace(' ', '_', $tipoMovimiento)) . '_' . 
                         preg_replace('/[^a-z0-9_]/', '', strtolower($trabajadorRecibe->getNombre())) . '_' . 
                         time() . '_' . uniqid() . '.pdf';
        $rutaSalida = __DIR__ . '/pdfs/' . $nombreArchivo;
        
        if (!file_exists(__DIR__ . '/pdfs')) {
            mkdir(__DIR__ . '/pdfs', 0775, true);
        }
        
        $generador = new GeneradorResguardoPDF();
        $generador->generar($trabajadorRecibe, $bienesSeleccionados, $datosAdicionales, $rutaSalida);
        
        $archivosGenerados[] = array(
            'tipo' => $tipoMovimiento,
            'ruta' => $rutaSalida,
            'nombre' => $nombreArchivo
        );
    }
    
    // Commit de todas las transacciones
    $pdo->commit();
    
    // Si solo hay un archivo, descargarlo directamente
    if (count($archivosGenerados) === 1) {
        $archivo = $archivosGenerados[0];
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $archivo['nombre'] . '"');
        header('Content-Length: ' . filesize($archivo['ruta']));
        readfile($archivo['ruta']);
        
        register_shutdown_function(function() use ($archivo) {
            if (file_exists($archivo['ruta'])) {
                sleep(5);
                @unlink($archivo['ruta']);
            }
        });
    } else {
        // Si hay múltiples archivos, crear un ZIP
        $zipNombre = 'documentos_' . time() . '.zip';
        $zipRuta = __DIR__ . '/pdfs/' . $zipNombre;
        
        $zip = new ZipArchive();
        if ($zip->open($zipRuta, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("No se pudo crear el archivo ZIP");
        }
        
        foreach ($archivosGenerados as $archivo) {
            $zip->addFile($archivo['ruta'], $archivo['nombre']);
        }
        
        $zip->close();
        
        // Descargar ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipNombre . '"');
        header('Content-Length: ' . filesize($zipRuta));
        readfile($zipRuta);
        
        // Limpiar archivos temporales
        register_shutdown_function(function() use ($archivosGenerados, $zipRuta) {
            sleep(5);
            foreach ($archivosGenerados as $archivo) {
                if (file_exists($archivo['ruta'])) {
                    @unlink($archivo['ruta']);
                }
            }
            if (file_exists($zipRuta)) {
                @unlink($zipRuta);
            }
        });
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("ERROR en procesar_pdf.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
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
?>