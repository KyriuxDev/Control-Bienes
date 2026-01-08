<?php
// public/procesar_pdf.php - VERSIÓN CON CAMPOS INDEPENDIENTES
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/generadores/GeneradorResguardoPDF.php';
require_once __DIR__ . '/generadores/GeneradorSalidaPDF.php';
require_once __DIR__ . '/generadores/GeneradorPrestamoPDF.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Infrastructure\Repository\MySQLMovimientoRepository;
use App\Infrastructure\Repository\MySQLDetalleMovimientoRepository;
use App\Infrastructure\Helper\FolioGenerator;
use App\Domain\Entity\Movimiento;
use App\Domain\Entity\Detalle_Movimiento;
use setasign\Fpdi\Tcpdf\Fpdi;

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
    $archivosTemporales = array();
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Obtener trabajadores
    $trabajadorRecibe = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_recibe']);
    $trabajadorEntrega = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_entrega']);
    
    if (!$trabajadorRecibe || !$trabajadorEntrega) {
        throw new Exception("Error: Trabajadores no encontrados");
    }
    
    // OBTENER ESTADO GLOBAL
    $estadoGeneral = 'Buenas condiciones'; // Por defecto
    
    if (isset($_POST['estado_general'])) {
        if ($_POST['estado_general'] === 'Otro' && isset($_POST['estado_otro']) && !empty($_POST['estado_otro'])) {
            $estadoGeneral = $_POST['estado_otro'];
        } else {
            $estadoGeneral = $_POST['estado_general'];
        }
    }
    
    // OBTENER SUJETO A DEVOLUCIÓN GLOBAL
    $sujetoDevolucionGlobal = isset($_POST['sujeto_devolucion_global']) ? intval($_POST['sujeto_devolucion_global']) : 0;
    
    // Preparar bienes CON DATOS GLOBALES
    $bienesSeleccionados = array();
    foreach ($_POST['bienes'] as $item) {
        if (empty($item['id_bien'])) continue;
        
        $bienObj = $bienRepo->obtenerPorId($item['id_bien']);
        if ($bienObj) {
            $bienesSeleccionados[] = array(
                'bien' => $bienObj,
                'cantidad' => isset($item['cantidad']) ? intval($item['cantidad']) : 1,
                'estado_fisico' => $estadoGeneral, // Usar estado global
                'sujeto_devolucion' => $sujetoDevolucionGlobal // Usar opción global
            );
        }
    }
    
    if (empty($bienesSeleccionados)) {
        throw new Exception("Debe seleccionar al menos un bien");
    }
    
    // Debug logging
    error_log("=== DEBUG PROCESAR PDF (CAMPOS INDEPENDIENTES) ===");
    error_log("Bienes seleccionados: " . count($bienesSeleccionados));
    error_log("Estado global: " . $estadoGeneral);
    error_log("Sujeto devolución global: " . $sujetoDevolucionGlobal);
    error_log("Fecha devolución préstamo: " . (isset($_POST['fecha_devolucion_prestamo']) ? $_POST['fecha_devolucion_prestamo'] : 'no establecida'));
    error_log("Fecha devolución constancia: " . (isset($_POST['fecha_devolucion_constancia']) ? $_POST['fecha_devolucion_constancia'] : 'no establecida'));
    
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
        
        // 2. Crear detalles para este movimiento CON DATOS GLOBALES
        foreach ($_POST['bienes'] as $item) {
            if (empty($item['id_bien'])) continue;
            
            $detalle = new Detalle_Movimiento();
            $detalle->setIdMovimiento($idMovimiento)
                   ->setIdBien($item['id_bien'])
                   ->setCantidad(isset($item['cantidad']) ? intval($item['cantidad']) : 1)
                   ->setEstadoFisico($estadoGeneral) // Usar estado global
                   ->setSujetoDevolucion($sujetoDevolucionGlobal); // Usar opción global
            
            $detalleRepo->persist($detalle);
        }
        
        // 3. Preparar datos para el PDF
        $datosAdicionales = array(
            'folio' => $folio,
            'fecha' => $_POST['fecha'],
            'lugar' => isset($_POST['lugar']) ? $_POST['lugar'] : 'Oaxaca de Juárez, Oaxaca',
            'recibe_resguardo' => $trabajadorRecibe->getNombre(),
            'entrega_resguardo' => $trabajadorEntrega->getNombre(),
            'cargo_entrega' => $trabajadorEntrega->getCargo(),
            'tipo_documento' => $tipoMovimiento,
            'departamento_per' => $trabajadorRecibe->getAdscripcion(),
            'responsable_control_administrativo' => $trabajadorEntrega->getNombre(),
            'matricula_administrativo' => $trabajadorEntrega->getMatricula(),
            'matricula_coordinacion' => $trabajadorRecibe->getMatricula(),
            'estado_general' => $estadoGeneral, // Estado global
            'sujeto_devolucion_global' => $sujetoDevolucionGlobal, // Opción global
            // Datos adicionales para Préstamo
            'dias_prestamo' => isset($_POST['dias_prestamo']) ? intval($_POST['dias_prestamo']) : null,
            'fecha_devolucion_prestamo' => isset($_POST['fecha_devolucion_prestamo']) ? $_POST['fecha_devolucion_prestamo'] : null,
            // Datos adicionales para Constancia de Salida
            'fecha_devolucion_constancia' => isset($_POST['fecha_devolucion_constancia']) ? $_POST['fecha_devolucion_constancia'] : null
        );
        
        // 4. Generar PDF temporal
        $directorioBase = __DIR__ . '/pdfs';
        
        // Asegurar que existe el directorio base
        if (!file_exists($directorioBase)) {
            mkdir($directorioBase, 0775, true);
        }
        
        $nombreArchivo = strtolower(str_replace(' ', '_', $tipoMovimiento)) . '_' . 
                         time() . '_' . uniqid() . '.pdf';
        $rutaTemporal = $directorioBase . '/' . $nombreArchivo;
        
        // Seleccionar generador según el tipo
        if ($tipoMovimiento === 'Resguardo') {
            $generador = new GeneradorResguardoPDF();
        } elseif ($tipoMovimiento === 'Constancia de salida') {
            $generador = new GeneradorSalidaPDF();
        } elseif ($tipoMovimiento === 'Prestamo') {
            $generador = new GeneradorPrestamoPDF();
        }
        
        $generador->generar($trabajadorRecibe, $bienesSeleccionados, $datosAdicionales, $rutaTemporal);
        
        $archivosTemporales[] = array(
            'tipo' => $tipoMovimiento,
            'ruta' => $rutaTemporal,
            'nombre' => $nombreArchivo
        );
    }
    
    // Commit de todas las transacciones
    $pdo->commit();
    
    // Si solo hay un archivo, descargarlo directamente
    if (count($archivosTemporales) === 1) {
        $archivo = $archivosTemporales[0];
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $archivo['tipo'] . '_' . $folio . '.pdf"');
        header('Content-Length: ' . filesize($archivo['ruta']));
        readfile($archivo['ruta']);
        
        register_shutdown_function(function() use ($archivo) {
            if (file_exists($archivo['ruta'])) {
                sleep(5);
                @unlink($archivo['ruta']);
            }
        });
    } else {
        // Si hay múltiples archivos, combinarlos en un solo PDF
        error_log("=== COMBINANDO MÚLTIPLES PDFs ===");
        error_log("Número de archivos a combinar: " . count($archivosTemporales));
        
        $pdfCombinado = new Fpdi();
        $pdfCombinado->setPrintHeader(false);
        $pdfCombinado->setPrintFooter(false);
        
        foreach ($archivosTemporales as $archivo) {
            error_log("Procesando archivo: " . $archivo['ruta']);
            
            if (!file_exists($archivo['ruta'])) {
                error_log("ERROR: Archivo no existe: " . $archivo['ruta']);
                continue;
            }
            
            try {
                // Obtener el número de páginas del PDF
                $numPaginas = $pdfCombinado->setSourceFile($archivo['ruta']);
                error_log("Archivo tiene $numPaginas páginas");
                
                // Importar cada página
                for ($i = 1; $i <= $numPaginas; $i++) {
                    $pdfCombinado->AddPage();
                    $templateId = $pdfCombinado->importPage($i);
                    $pdfCombinado->useTemplate($templateId, 0, 0, null, null, true);
                }
            } catch (Exception $e) {
                error_log("ERROR al procesar archivo: " . $e->getMessage());
            }
        }
        
        // Guardar PDF combinado - RUTA SIMPLE SIN SUBDIRECTORIOS
        $directorioBase = __DIR__ . '/pdfs';
        
        // Limpiar el folio para usarlo como nombre de archivo (sin barras)
        $folioLimpio = str_replace('/', '_', $folio);
        $nombreCombinado = 'docs_' . $folioLimpio . '_' . time() . '.pdf';
        
        // Usar ruta absoluta real
        $rutaAbsoluta = realpath($directorioBase);
        if ($rutaAbsoluta === false) {
            throw new Exception("No se puede obtener la ruta absoluta de: " . $directorioBase);
        }
        
        $rutaCombinada = $rutaAbsoluta . '/' . $nombreCombinado;
        
        error_log("Guardando PDF combinado en: " . $rutaCombinada);
        error_log("Directorio existe: " . (file_exists($rutaAbsoluta) ? 'SI' : 'NO'));
        error_log("Directorio escribible: " . (is_writable($rutaAbsoluta) ? 'SI' : 'NO'));
        
        // Intentar guardar
        try {
            $pdfCombinado->Output($rutaCombinada, 'F');
            error_log("PDF combinado guardado exitosamente");
        } catch (Exception $e) {
            error_log("ERROR al guardar PDF: " . $e->getMessage());
            throw new Exception("Error al guardar PDF combinado: " . $e->getMessage());
        }
        
        if (!file_exists($rutaCombinada)) {
            throw new Exception("El archivo PDF no se creó correctamente en: " . $rutaCombinada);
        }
        
        // Descargar PDF combinado
        error_log("Enviando archivo al navegador: " . basename($rutaCombinada));
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Documentos_' . $folioLimpio . '.pdf"');
        header('Content-Length: ' . filesize($rutaCombinada));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($rutaCombinada);
        
        // Limpiar archivos temporales
        register_shutdown_function(function() use ($archivosTemporales, $rutaCombinada) {
            sleep(5);
            foreach ($archivosTemporales as $archivo) {
                if (file_exists($archivo['ruta'])) {
                    @unlink($archivo['ruta']);
                }
            }
            if (file_exists($rutaCombinada)) {
                @unlink($rutaCombinada);
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