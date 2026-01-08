<?php
// public/procesar_pdf.php - VERSIÓN CON DEBUGGING MEJORADO
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Log inicial
error_log("=== PROCESAR PDF INICIADO ===");
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
use App\Infrastructure\Repository\MySQLMovimientoRepository;
use App\Infrastructure\Repository\MySQLDetalleMovimientoRepository;
use App\Infrastructure\Helper\FolioGenerator;
use App\Domain\Entity\Movimiento;
use App\Domain\Entity\Detalle_Movimiento;
use setasign\Fpdi\Tcpdf\Fpdi;

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
                <h1 class="text-xl font-bold">Error al Generar PDF</h1>
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
                <button onclick="window.history.back()" class="flex-1 bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 font-semibold">
                    ← Volver al Formulario
                </button>
                <a href="test_pdf.php" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-center font-semibold">
                    Ejecutar Diagnóstico
                </a>
            </div>
        </div>
    </body>
    </html>';
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mostrarError('Método no permitido', 'Solo se aceptan peticiones POST');
}

try {
    // Verificar que los archivos de plantillas existan
    $plantillas = [
        'Resguardo' => __DIR__ . '/../templates/resguardo.pdf',
        'Prestamo' => __DIR__ . '/../templates/prestamo.pdf',
        'Constancia de salida' => __DIR__ . '/../templates/salidaBien.pdf'
    ];
    
    foreach ($plantillas as $tipo => $ruta) {
        if (!file_exists($ruta)) {
            mostrarError(
                "Plantilla PDF no encontrada para: $tipo",
                "Ruta esperada: $ruta\nVerifique que el archivo existe y tiene los permisos correctos."
            );
        }
    }
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $trabajadorRepo = new MySQLTrabajadorRepository($pdo);
    $bienRepo = new MySQLBienRepository($pdo);
    $movimientoRepo = new MySQLMovimientoRepository($pdo);
    $detalleRepo = new MySQLDetalleMovimientoRepository($pdo);
    $folioGenerator = new FolioGenerator($pdo);

    // Validar datos del formulario
    if (!isset($_POST['tipos_movimiento']) || empty($_POST['tipos_movimiento'])) {
        mostrarError("Debe seleccionar al menos un tipo de documento");
    }
    
    if (empty($_POST['matricula_recibe'])) {
        mostrarError("Debe seleccionar el trabajador que recibe");
    }
    
    if (empty($_POST['matricula_entrega'])) {
        mostrarError("Debe seleccionar el trabajador que entrega");
    }
    
    if (!isset($_POST['bienes']) || empty($_POST['bienes'])) {
        mostrarError("Debe agregar al menos un bien");
    }
    
    error_log("Validaciones iniciales completadas");
    
    // GENERAR FOLIO AUTOMÁTICAMENTE
    $folio = $folioGenerator->generarFolioUnico();
    error_log("Folio generado: $folio");
    
    $tiposMovimiento = $_POST['tipos_movimiento'];
    $archivosTemporales = array();
    
    // Iniciar transacción
    $pdo->beginTransaction();
    error_log("Transacción iniciada");
    
    // Obtener trabajadores
    $trabajadorRecibe = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_recibe']);
    $trabajadorEntrega = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_entrega']);
    
    if (!$trabajadorRecibe || !$trabajadorEntrega) {
        throw new Exception("Error: Trabajadores no encontrados");
    }
    
    error_log("Trabajadores obtenidos correctamente");
    
    // OBTENER ESTADO GLOBAL
    $estadoGeneral = 'Buenas condiciones';
    
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
    $bienesCount = 0;
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
            $bienesCount++;
        }
    }
    
    if (empty($bienesSeleccionados)) {
        throw new Exception("Debe seleccionar al menos un bien válido");
    }
    
    error_log("Bienes procesados: $bienesCount");
    
    // Asegurar que existe el directorio de PDFs
    $directorioBase = __DIR__ . '/pdfs';
    if (!file_exists($directorioBase)) {
        if (!mkdir($directorioBase, 0775, true)) {
            throw new Exception("No se pudo crear el directorio de PDFs: $directorioBase");
        }
        error_log("Directorio de PDFs creado: $directorioBase");
    }
    
    // Verificar que el directorio es escribible
    if (!is_writable($directorioBase)) {
        throw new Exception("El directorio de PDFs no es escribible: $directorioBase");
    }
    
    // Generar cada tipo de documento seleccionado
    foreach ($tiposMovimiento as $tipoMovimiento) {
        error_log("Generando PDF para: $tipoMovimiento");
        
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
        error_log("Movimiento creado con ID: $idMovimiento");
        
        // 2. Crear detalles para este movimiento
        foreach ($_POST['bienes'] as $item) {
            if (empty($item['id_bien'])) continue;
            
            $detalle = new Detalle_Movimiento();
            $detalle->setIdMovimiento($idMovimiento)
                   ->setIdBien($item['id_bien'])
                   ->setCantidad(isset($item['cantidad']) ? intval($item['cantidad']) : 1)
                   ->setEstadoFisico($estadoGeneral)
                   ->setSujetoDevolucion($sujetoDevolucionGlobal);
            
            $detalleRepo->persist($detalle);
        }
        
        error_log("Detalles del movimiento creados");
        
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
            'estado_general' => $estadoGeneral,
            'sujeto_devolucion_global' => $sujetoDevolucionGlobal,
            'dias_prestamo' => isset($_POST['dias_prestamo']) ? intval($_POST['dias_prestamo']) : null,
            'fecha_devolucion_prestamo' => isset($_POST['fecha_devolucion_prestamo']) ? $_POST['fecha_devolucion_prestamo'] : null,
            'fecha_devolucion_constancia' => isset($_POST['fecha_devolucion_constancia']) ? $_POST['fecha_devolucion_constancia'] : null
        );
        
        // 4. Generar PDF temporal
        $nombreArchivo = strtolower(str_replace(' ', '_', $tipoMovimiento)) . '_' . 
                         time() . '_' . uniqid() . '.pdf';
        $rutaTemporal = $directorioBase . '/' . $nombreArchivo;
        
        error_log("Generando PDF en: $rutaTemporal");
        
        // Seleccionar generador según el tipo
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
            throw new Exception("El PDF no se generó correctamente: $rutaTemporal");
        }
        
        $tamano = filesize($rutaTemporal);
        error_log("PDF generado exitosamente. Tamaño: $tamano bytes");
        
        if ($tamano < 100) {
            throw new Exception("El PDF generado está vacío o corrupto (tamaño: $tamano bytes)");
        }
        
        $archivosTemporales[] = array(
            'tipo' => $tipoMovimiento,
            'ruta' => $rutaTemporal,
            'nombre' => $nombreArchivo
        );
    }
    
    // Commit de todas las transacciones
    $pdo->commit();
    error_log("Transacción completada exitosamente");
    
    // Si solo hay un archivo, descargarlo directamente
    if (count($archivosTemporales) === 1) {
        $archivo = $archivosTemporales[0];
        
        error_log("Enviando archivo único al navegador");
        
        // Limpiar cualquier salida previa
        if (ob_get_level()) ob_end_clean();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $archivo['tipo'] . '_' . $folio . '.pdf"');
        header('Content-Length: ' . filesize($archivo['ruta']));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($archivo['ruta']);
        
        register_shutdown_function(function() use ($archivo) {
            if (file_exists($archivo['ruta'])) {
                sleep(5);
                @unlink($archivo['ruta']);
            }
        });
    } else {
        // Si hay múltiples archivos, combinarlos
        error_log("Combinando múltiples PDFs: " . count($archivosTemporales));
        
        $pdfCombinado = new Fpdi();
        $pdfCombinado->setPrintHeader(false);
        $pdfCombinado->setPrintFooter(false);
        
        foreach ($archivosTemporales as $archivo) {
            error_log("Añadiendo archivo al PDF combinado: " . $archivo['ruta']);
            
            if (!file_exists($archivo['ruta'])) {
                error_log("ERROR: Archivo no existe: " . $archivo['ruta']);
                continue;
            }
            
            try {
                $numPaginas = $pdfCombinado->setSourceFile($archivo['ruta']);
                error_log("Archivo tiene $numPaginas páginas");
                
                for ($i = 1; $i <= $numPaginas; $i++) {
                    $pdfCombinado->AddPage();
                    $templateId = $pdfCombinado->importPage($i);
                    $pdfCombinado->useTemplate($templateId, 0, 0, null, null, true);
                }
            } catch (Exception $e) {
                error_log("ERROR al procesar archivo: " . $e->getMessage());
                throw $e;
            }
        }
        
        $folioLimpio = str_replace('/', '_', $folio);
        $nombreCombinado = 'docs_' . $folioLimpio . '_' . time() . '.pdf';
        $rutaCombinada = $directorioBase . '/' . $nombreCombinado;
        
        error_log("Guardando PDF combinado en: $rutaCombinada");
        
        $pdfCombinado->Output($rutaCombinada, 'F');
        
        if (!file_exists($rutaCombinada)) {
            throw new Exception("El archivo PDF combinado no se creó correctamente");
        }
        
        $tamanoCombinado = filesize($rutaCombinada);
        error_log("PDF combinado guardado. Tamaño: $tamanoCombinado bytes");
        
        // Limpiar cualquier salida previa
        if (ob_get_level()) ob_end_clean();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Documentos_' . $folioLimpio . '.pdf"');
        header('Content-Length: ' . filesize($rutaCombinada));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($rutaCombinada);
        
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
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("EXCEPTION: " . $e->getMessage());
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