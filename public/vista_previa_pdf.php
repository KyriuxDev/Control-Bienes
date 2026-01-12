<?php
// public/vista_previa_pdf.php - VERSIÓN CON DEBUGGING MEJORADO
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Log inicial
error_log("=== VISTA PREVIA PDF INICIADA ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/generadores/GeneradorResguardoPDF.php';
require_once __DIR__ . '/generadores/GeneradorSalidaPDF.php';
require_once __DIR__ . '/generadores/GeneradorPrestamoPDF.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Infrastructure\Helper\FolioGenerator;
use setasign\Fpdi\Tcpdf\Fpdi;

// Función para mostrar error HTML
function mostrarError($mensaje, $detalles = '') {
    error_log("ERROR VISTA PREVIA: $mensaje");
    if ($detalles) {
        error_log("Detalles: $detalles");
    }
    
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error en Vista Previa</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-2xl w-full">
            <div class="flex items-center gap-3 text-red-600 mb-4">
                <svg class="w-8 h-8 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h1 class="text-xl font-bold">Error en Vista Previa</h1>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 mb-2"><strong>Mensaje:</strong></p>
                <p class="text-gray-600 text-sm bg-red-50 border-l-4 border-red-500 p-3 rounded">' . htmlspecialchars($mensaje) . '</p>
            </div>';
    
    if ($detalles) {
        echo '<details class="mb-4">
                <summary class="cursor-pointer text-sm font-semibold text-gray-600 hover:text-gray-800">Ver detalles técnicos</summary>
                <pre class="mt-2 p-4 bg-gray-100 text-xs overflow-auto max-h-64">' . htmlspecialchars($detalles) . '</pre>
              </details>';
    }
    
    echo '<div class="flex gap-2">
                <button onclick="window.close()" class="flex-1 bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 transition">
                    Cerrar
                </button>
                <button onclick="window.history.back()" class="flex-1 bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition">
                    Volver
                </button>
                <a href="test_pdf.php" target="_blank" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-center">
                    Diagnóstico
                </a>
            </div>
        </div>
    </body>
    </html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mostrarError('Método no permitido', 'Solo se aceptan peticiones POST.');
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $trabajadorRepo = new MySQLTrabajadorRepository($pdo);
    $bienRepo = new MySQLBienRepository($pdo);
    $folioGenerator = new FolioGenerator($pdo);
    
    // Validar que haya tipos de movimiento seleccionados
    if (!isset($_POST['tipos_movimiento']) || empty($_POST['tipos_movimiento'])) {
        throw new Exception("Debe seleccionar al menos un tipo de documento");
    }
    
    // Validar fecha
    if (empty($_POST['fecha'])) {
        throw new Exception("La fecha es obligatoria");
    }
    
    // Validar lugar
    if (!isset($_POST['lugar'])) {
        $_POST['lugar'] = 'Oaxaca de Juárez, Oaxaca';
    }
    
    error_log("Validaciones iniciales completadas");
    
    // GENERAR FOLIO TEMPORAL PARA VISTA PREVIA
    $folio = $folioGenerator->generarFolio();
    error_log("Folio temporal generado: $folio");
    
    $tiposMovimiento = $_POST['tipos_movimiento'];
    
    // 1. Obtener Trabajadores
    if (empty($_POST['matricula_recibe']) || empty($_POST['matricula_entrega'])) {
        throw new Exception("Debe seleccionar ambos trabajadores (quien recibe y quien entrega)");
    }
    
    $trabajadorRecibe = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_recibe']);
    $trabajadorEntrega = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_entrega']);

    if (!$trabajadorRecibe) {
        throw new Exception("Trabajador que recibe no encontrado (matrícula: {$_POST['matricula_recibe']})");
    }
    
    if (!$trabajadorEntrega) {
        throw new Exception("Trabajador que entrega no encontrado (matrícula: {$_POST['matricula_entrega']})");
    }
    
    error_log("Trabajadores obtenidos correctamente");

    // 2. OBTENER ESTADO GLOBAL
    $estadoGeneral = 'Buenas condiciones';
    
    if (isset($_POST['estado_general'])) {
        if ($_POST['estado_general'] === 'Otro' && isset($_POST['estado_otro']) && !empty($_POST['estado_otro'])) {
            $estadoGeneral = $_POST['estado_otro'];
        } else {
            $estadoGeneral = $_POST['estado_general'];
        }
    }
    
    // 3. OBTENER SUJETO A DEVOLUCIÓN GLOBAL
    $sujetoDevolucionGlobal = isset($_POST['sujeto_devolucion_global']) ? intval($_POST['sujeto_devolucion_global']) : 0;
    
    // 4. Obtener Bienes CON DATOS GLOBALES
    if (!isset($_POST['bienes']) || empty($_POST['bienes'])) {
        throw new Exception("Debe agregar al menos un bien");
    }
    
    $bienesSeleccionados = array();
    foreach ($_POST['bienes'] as $item) {
        if (empty($item['id_bien'])) continue;
        
        $bienObj = $bienRepo->obtenerPorId($item['id_bien']);
        if ($bienObj) {
            $bienesSeleccionados[] = array(
                'bien' => $bienObj,
                'cantidad' => isset($item['cantidad']) ? intval($item['cantidad']) : 1,
                'estado_fisico' => $estadoGeneral,
                'sujeto_devolucion' => $sujetoDevolucionGlobal
            );
        }
    }
    
    // Verificar que haya al menos un bien
    if (empty($bienesSeleccionados)) {
        throw new Exception("Debe seleccionar al menos un bien válido");
    }
    
    error_log("Bienes procesados: " . count($bienesSeleccionados));

    // 5. Preparar datos adicionales
    $datosAdicionales = array(
        'folio' => $folio,
        'fecha' => $_POST['fecha'],
        'lugar' => isset($_POST['lugar']) ? $_POST['lugar'] : 'Oaxaca de Juárez, Oaxaca',
        'recibe_resguardo' => $trabajadorRecibe->getNombre(),
        'entrega_resguardo' => $trabajadorEntrega->getNombre(),
        'cargo_entrega' => $trabajadorEntrega->getCargo(),
        'tipo_documento' => '',
        'departamento_per' => $trabajadorRecibe->getAdscripcion(),
        'responsable_control_administrativo' => $trabajadorEntrega->getNombre(),
        'matricula_administrativo' => $trabajadorEntrega->getMatricula(),
        'matricula_coordinacion' => $trabajadorRecibe->getMatricula(),
        'estado_general' => $estadoGeneral,
        'sujeto_devolucion_global' => $sujetoDevolucionGlobal,
        'dias_prestamo' => isset($_POST['dias_prestamo']) ? intval($_POST['dias_prestamo']) : null,
        'fecha_devolucion_prestamo' => isset($_POST['fecha_devolucion_prestamo']) ? $_POST['fecha_devolucion_prestamo'] : null,
        'fecha_devolucion_constancia' => isset($_POST['fecha_devolucion_constancia']) ? $_POST['fecha_devolucion_constancia'] : null,
        // NUEVO: También pasar como fecha_devolucion para compatibilidad
        'fecha_devolucion' => isset($_POST['fecha_devolucion_constancia']) ? $_POST['fecha_devolucion_constancia'] : null
    );

    // 6. Generar PDFs temporales
    $archivosTemporales = array();
    
    $directorioBase = __DIR__ . '/pdfs';
    if (!file_exists($directorioBase)) {
        if (!mkdir($directorioBase, 0775, true)) {
            throw new Exception("No se pudo crear el directorio de PDFs: $directorioBase");
        }
    }
    
    if (!is_writable($directorioBase)) {
        throw new Exception("El directorio de PDFs no es escribible: $directorioBase");
    }

    foreach ($tiposMovimiento as $tipoMovimiento) {
        error_log("Generando vista previa para: $tipoMovimiento");
        
        $rutaTemporal = $directorioBase . '/preview_' . strtolower(str_replace(' ', '_', $tipoMovimiento)) . '_' . time() . '_' . uniqid() . '.pdf';
        
        // Seleccionar generador según el tipo
        if ($tipoMovimiento === 'Resguardo') {
            $generador = new GeneradorResguardoPDF();
        } elseif ($tipoMovimiento === 'Constancia de salida') {
            $generador = new GeneradorSalidaPDF();
        } elseif ($tipoMovimiento === 'Prestamo') {
            $generador = new GeneradorPrestamoPDF();
        } else {
            continue;
        }
        
        $generador->generar($trabajadorRecibe, $bienesSeleccionados, $datosAdicionales, $rutaTemporal);
        
        if (!file_exists($rutaTemporal)) {
            throw new Exception("Error al generar el PDF temporal para " . $tipoMovimiento);
        }
        
        $tamano = filesize($rutaTemporal);
        error_log("PDF generado: $rutaTemporal (tamaño: $tamano bytes)");
        
        if ($tamano < 100) {
            throw new Exception("El PDF generado está vacío (tamaño: $tamano bytes)");
        }
        
        $archivosTemporales[] = array(
            'tipo' => $tipoMovimiento,
            'ruta' => $rutaTemporal,
            'nombre' => basename($rutaTemporal)
        );
    }

    // 7. Si hay un solo archivo, mostrarlo directamente
    if (count($archivosTemporales) === 1) {
        $archivo = $archivosTemporales[0];
        
        error_log("Enviando archivo único para vista previa");
        
        // Limpiar cualquier salida previa
        if (ob_get_level()) ob_end_clean();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="Vista_Previa_' . str_replace(' ', '_', $archivo['tipo']) . '.pdf"');
        header('Content-Length: ' . filesize($archivo['ruta']));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($archivo['ruta']);
        
        register_shutdown_function(function() use ($archivo) {
            if (file_exists($archivo['ruta'])) {
                sleep(2);
                @unlink($archivo['ruta']);
            }
        });
    } else {
        // Si hay múltiples archivos, combinarlos
        error_log("Combinando múltiples PDFs para vista previa: " . count($archivosTemporales));
        
        $pdfCombinado = new Fpdi();
        $pdfCombinado->setPrintHeader(false);
        $pdfCombinado->setPrintFooter(false);
        
        foreach ($archivosTemporales as $archivo) {
            error_log("Añadiendo archivo: " . $archivo['ruta']);
            
            if (!file_exists($archivo['ruta'])) {
                error_log("ERROR: Archivo no existe");
                continue;
            }
            
            try {
                $numPaginas = $pdfCombinado->setSourceFile($archivo['ruta']);
                error_log("Páginas: " . $numPaginas);
                
                for ($i = 1; $i <= $numPaginas; $i++) {
                    $pdfCombinado->AddPage();
                    $templateId = $pdfCombinado->importPage($i);
                    $pdfCombinado->useTemplate($templateId, 0, 0, null, null, true);
                }
            } catch (Exception $e) {
                error_log("ERROR al combinar: " . $e->getMessage());
                throw $e;
            }
        }
        
        $rutaCombinada = $directorioBase . '/preview_combined_' . time() . '_' . uniqid() . '.pdf';
        
        error_log("Guardando PDF combinado: $rutaCombinada");
        $pdfCombinado->Output($rutaCombinada, 'F');
        
        if (!file_exists($rutaCombinada)) {
            throw new Exception("No se pudo guardar el PDF combinado");
        }
        
        $tamanoCombinado = filesize($rutaCombinada);
        error_log("PDF combinado guardado (tamaño: $tamanoCombinado bytes)");
        
        // Limpiar cualquier salida previa
        if (ob_get_level()) ob_end_clean();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="Vista_Previa_Documentos.pdf"');
        header('Content-Length: ' . filesize($rutaCombinada));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($rutaCombinada);
        
        register_shutdown_function(function() use ($archivosTemporales, $rutaCombinada) {
            sleep(2);
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
    error_log("EXCEPTION en vista previa: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    mostrarError(
        $e->getMessage(),
        "Archivo: " . $e->getFile() . "\n" .
        "Línea: " . $e->getLine() . "\n\n" .
        $e->getTraceAsString()
    );
}

exit;
?>