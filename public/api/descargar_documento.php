<?php
// public/api/descargar_documento.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../generadores/GeneradorResguardoPDF.php';
require_once __DIR__ . '/../generadores/GeneradorSalidaPDF.php';
require_once __DIR__ . '/../generadores/GeneradorPrestamoPDF.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLMovimientoRepository;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLDetalleMovimientoRepository;
use App\Infrastructure\Repository\MySQLBienRepository;

// Función para mostrar error HTML
function mostrarError($mensaje, $detalles = '') {
    error_log("ERROR: $mensaje");
    if ($detalles) {
        error_log("Detalles: $detalles");
    }
    
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-2xl w-full">
            <div class="flex items-center gap-3 text-red-600 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h1 class="text-xl font-bold">Error al Descargar PDF</h1>
            </div>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                <p class="text-red-800 font-medium">' . htmlspecialchars($mensaje) . '</p>
            </div>';
    
    if ($detalles) {
        echo '<details class="mb-4">
                <summary class="cursor-pointer text-sm font-semibold text-gray-600 hover:text-gray-800">Ver detalles técnicos</summary>
                <pre class="mt-2 p-4 bg-gray-100 text-xs overflow-auto max-h-64">' . htmlspecialchars($detalles) . '</pre>
              </details>';
    }
    
    echo '<div class="flex gap-3">
                <button onclick="window.close()" class="flex-1 bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 font-semibold">
                    Cerrar
                </button>
            </div>
        </div>
    </body>
    </html>';
    exit;
}

// Verificar método GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    mostrarError('Método no permitido', 'Solo se aceptan peticiones GET');
}

try {
    // Validar ID
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("ID de documento no proporcionado");
    }
    
    $idMovimiento = intval($_GET['id']);
    error_log("=== DESCARGAR DOCUMENTO ID: $idMovimiento ===");
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $movimientoRepo = new MySQLMovimientoRepository($pdo);
    $trabajadorRepo = new MySQLTrabajadorRepository($pdo);
    $detalleRepo = new MySQLDetalleMovimientoRepository($pdo);
    $bienRepo = new MySQLBienRepository($pdo);
    
    // Obtener el movimiento
    $movimiento = $movimientoRepo->obtenerPorId($idMovimiento);
    
    if (!$movimiento) {
        throw new Exception("Documento no encontrado con ID: $idMovimiento");
    }
    
    error_log("Movimiento encontrado: " . $movimiento->getFolio());
    
    // Obtener trabajadores
    $trabajadorRecibe = $trabajadorRepo->obtenerPorMatricula($movimiento->getMatriculaRecibe());
    $trabajadorEntrega = $trabajadorRepo->obtenerPorMatricula($movimiento->getMatriculaEntrega());
    
    if (!$trabajadorRecibe || !$trabajadorEntrega) {
        throw new Exception("Error al cargar información de trabajadores");
    }
    
    error_log("Trabajadores cargados correctamente");
    
    // Obtener detalles del movimiento con información de bienes
    $detalles = $detalleRepo->buscarPorMovimiento($idMovimiento);
    
    if (empty($detalles)) {
        throw new Exception("El documento no tiene bienes asociados");
    }
    
    error_log("Detalles encontrados: " . count($detalles));
    
    // OBTENER ESTADO GLOBAL (del primer detalle, ya que son globales)
    $estadoGeneral = $detalles[0]->getEstadoFisico();
    $sujetoDevolucionGlobal = $detalles[0]->getSujetoDevolucion();
    
    // Preparar array de bienes
    $bienesSeleccionados = array();
    foreach ($detalles as $detalle) {
        $bien = $bienRepo->obtenerPorId($detalle->getIdBien());
        
        if ($bien) {
            $bienesSeleccionados[] = array(
                'bien' => $bien,
                'cantidad' => $detalle->getCantidad(),
                'estado_fisico' => $detalle->getEstadoFisico(),
                'sujeto_devolucion' => $detalle->getSujetoDevolucion()
            );
        }
    }
    
    error_log("Bienes preparados: " . count($bienesSeleccionados));
    
    // Preparar datos adicionales
    $datosAdicionales = array(
        'folio' => $movimiento->getFolio(),
        'fecha' => $movimiento->getFecha(),
        'lugar' => $movimiento->getLugar(),
        'area' => $movimiento->getArea(),
        'recibe_resguardo' => $trabajadorRecibe->getNombre(),
        'entrega_resguardo' => $trabajadorEntrega->getNombre(),
        'cargo_entrega' => $trabajadorEntrega->getCargo(),
        'tipo_documento' => $movimiento->getTipoMovimiento(),
        'departamento_per' => $trabajadorRecibe->getAdscripcion(),
        'responsable_control_administrativo' => $trabajadorEntrega->getNombre(),
        'matricula_administrativo' => $trabajadorEntrega->getMatricula(),
        'matricula_coordinacion' => $trabajadorRecibe->getMatricula(),
        'estado_general' => $estadoGeneral,
        'sujeto_devolucion_global' => $sujetoDevolucionGlobal,
        'dias_prestamo' => $movimiento->getDiasPrestamo(),
        'fecha_devolucion_prestamo' => null, // No se almacena en BD
        'fecha_devolucion_constancia' => null // No se almacena en BD
    );
    
    // Crear directorio temporal si no existe
    $directorioBase = __DIR__ . '/../pdfs';
    if (!file_exists($directorioBase)) {
        if (!mkdir($directorioBase, 0775, true)) {
            throw new Exception("No se pudo crear el directorio de PDFs");
        }
    }
    
    // Generar PDF según el tipo
    $tipoMovimiento = $movimiento->getTipoMovimiento();
    $nombreArchivo = strtolower(str_replace(' ', '_', $tipoMovimiento)) . '_' . 
                     str_replace('/', '_', $movimiento->getFolio()) . '_' . 
                     time() . '.pdf';
    $rutaTemporal = $directorioBase . '/' . $nombreArchivo;
    
    error_log("Generando PDF: $rutaTemporal");
    
    // Seleccionar generador
    if ($tipoMovimiento === 'Resguardo') {
        $generador = new GeneradorResguardoPDF();
    } elseif ($tipoMovimiento === 'Constancia de salida') {
        $generador = new GeneradorSalidaPDF();
    } elseif ($tipoMovimiento === 'Prestamo') {
        $generador = new GeneradorPrestamoPDF();
    } else {
        throw new Exception("Tipo de movimiento no reconocido: $tipoMovimiento");
    }
    
    $generador->generar($trabajadorRecibe, $bienesSeleccionados, $datosAdicionales, $rutaTemporal);
    
    // Verificar que el PDF se generó
    if (!file_exists($rutaTemporal)) {
        throw new Exception("El PDF no se generó correctamente");
    }
    
    $tamano = filesize($rutaTemporal);
    error_log("PDF generado exitosamente. Tamaño: $tamano bytes");
    
    if ($tamano < 100) {
        throw new Exception("El PDF generado está vacío o corrupto");
    }
    
    // Limpiar cualquier salida previa
    if (ob_get_level()) ob_end_clean();
    
    // Enviar PDF al navegador
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $tipoMovimiento . '_' . str_replace('/', '_', $movimiento->getFolio()) . '.pdf"');
    header('Content-Length: ' . filesize($rutaTemporal));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($rutaTemporal);
    
    // Eliminar archivo temporal después de enviarlo
    register_shutdown_function(function() use ($rutaTemporal) {
        if (file_exists($rutaTemporal)) {
            sleep(2);
            @unlink($rutaTemporal);
        }
    });
    
} catch (Exception $e) {
    error_log("EXCEPTION en descargar_documento.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    mostrarError(
        $e->getMessage(),
        "Archivo: " . $e->getFile() . "\n" .
        "Línea: " . $e->getLine() . "\n\n" .
        $e->getTraceAsString()
    );
}

exit;