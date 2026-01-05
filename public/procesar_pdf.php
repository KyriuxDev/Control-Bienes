<?php
// procesar_pdf.php - Procesa el formulario y genera el PDF correspondiente
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/generadores/GeneradorPrestamoPDF.php';
require_once __DIR__ . '/generadores/GeneradorResguardoPDF.php';
require_once __DIR__ . '/generadores/GeneradorSalidaPDF.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;

// Función para registrar errores detallados
function logError($mensaje, $contexto = []) {
    $logMsg = date('[Y-m-d H:i:s] ') . $mensaje;
    if (!empty($contexto)) {
        $logMsg .= ' - Contexto: ' . json_encode($contexto, JSON_UNESCAPED_UNICODE);
    }
    error_log($logMsg);
}

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: generador_documentos.php');
    exit;
}

try {
    // Conexión a base de datos
    $db  = Database::getInstance();
    $pdo = $db->getConnection();

    $trabajadorRepo = new MySQLTrabajadorRepository($pdo);
    $bienRepo       = new MySQLBienRepository($pdo);

    // Obtener y validar datos del formulario
    $tipoDocumento = isset($_POST['tipo_documento']) ? trim($_POST['tipo_documento']) : '';
    $trabajadorId  = isset($_POST['trabajador_id']) ? intval($_POST['trabajador_id']) : 0;

    logError('Iniciando generación de PDF', [
        'tipo' => $tipoDocumento,
        'trabajador_id' => $trabajadorId
    ]);

    // Validar datos básicos
    if (empty($tipoDocumento)) {
        throw new Exception('Debe seleccionar el tipo de documento');
    }

    if ($trabajadorId <= 0) {
        throw new Exception('Debe seleccionar un trabajador válido');
    }

    // Validar tipo de documento
    $tiposValidos = ['prestamo', 'resguardo', 'salida'];
    if (!in_array($tipoDocumento, $tiposValidos)) {
        throw new Exception('Tipo de documento no válido');
    }

    // Obtener datos del trabajador
    $trabajador = $trabajadorRepo->getById($trabajadorId);
    if (!$trabajador) {
        throw new Exception('Trabajador no encontrado con ID: ' . $trabajadorId);
    }

    logError('Trabajador encontrado', [
        'nombre' => $trabajador->getNombre(),
        'matricula' => $trabajador->getMatricula()
    ]);

    // Procesar bienes
    $bienes = array();
    if (isset($_POST['bienes']) && is_array($_POST['bienes'])) {
        foreach ($_POST['bienes'] as $index => $bienData) {
            if (!empty($bienData['bien_id'])) {
                $bienId = intval($bienData['bien_id']);
                $bien = $bienRepo->getById($bienId);
                
                if ($bien) {
                    $cantidad = isset($bienData['cantidad']) ? intval($bienData['cantidad']) : 1;
                    
                    // Validar cantidad
                    if ($cantidad < 1) {
                        throw new Exception('La cantidad del bien debe ser mayor a 0');
                    }
                    
                    $bienes[] = array(
                        'bien'     => $bien,
                        'cantidad' => $cantidad
                    );
                    
                    logError('Bien agregado', [
                        'index' => $index,
                        'bien_id' => $bienId,
                        'descripcion' => $bien->getDescripcion(),
                        'cantidad' => $cantidad
                    ]);
                } else {
                    logError('Bien no encontrado', ['bien_id' => $bienId]);
                }
            }
        }
    }

    if (empty($bienes)) {
        throw new Exception('Debe agregar al menos un bien al documento');
    }

    logError('Total de bienes procesados: ' . count($bienes));

    // Crear directorio de PDFs si no existe
    $pdfDir = __DIR__ . '/pdfs';
    if (!is_dir($pdfDir)) {
        if (!mkdir($pdfDir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de PDFs');
        }
    }

    // Verificar permisos de escritura
    if (!is_writable($pdfDir)) {
        throw new Exception('El directorio de PDFs no tiene permisos de escritura');
    }

    // Generar PDF según el tipo de documento
    $nombreArchivo = '';
    $rutaPDF       = '';
    $generador     = null;
    $datosAdicionales = array();

    switch ($tipoDocumento) {

        case 'prestamo':
            $generador = new GeneradorPrestamoPDF();
            
            // Validar campos requeridos para préstamo
            if (empty($_POST['lugar_fecha_prestamo'])) {
                throw new Exception('El campo "Lugar y Fecha" es requerido para préstamos');
            }
            
            // Mapear campos del formulario
            $datosAdicionales = array(
                'fecha_emision'        => isset($_POST['fecha_emision_prestamo']) ? $_POST['fecha_emision_prestamo'] : date('Y-m-d'),
                'lugar_fecha'          => $_POST['lugar_fecha_prestamo'],
                'nota'                 => isset($_POST['nota_prestamo']) ? trim($_POST['nota_prestamo']) : '',
                'matricula_autoriza'   => isset($_POST['matricula_autoriza']) ? trim($_POST['matricula_autoriza']) : '',
                'matricula_recibe'     => isset($_POST['matricula_recibe']) ? trim($_POST['matricula_recibe']) : $trabajador->getMatricula()
            );
            
            $nombreArchivo = 'prestamo_' . $trabajador->getMatricula() . '_' . date('YmdHis') . '.pdf';
            break;

        case 'resguardo':
            $generador = new GeneradorResguardoPDF();
            
            // Validar campos requeridos para resguardo
            if (empty($_POST['lugar_fecha_resguardo'])) {
                throw new Exception('El campo "Lugar y Fecha" es requerido para resguardos');
            }
            
            // Mapear campos del formulario
            $datosAdicionales = array(
                'folio'        => isset($_POST['folio_resguardo']) && !empty($_POST['folio_resguardo']) 
                    ? trim($_POST['folio_resguardo']) 
                    : 'RES-' . date('YmdHis'),
                'lugar_fecha'  => $_POST['lugar_fecha_resguardo'],
                'notas'        => isset($_POST['notas_resguardo']) ? trim($_POST['notas_resguardo']) : '',
                'entrega_resguardo' => isset($_POST['entrega_resguardo']) ? trim($_POST['entrega_resguardo']) : '',
                'cargo_entrega' => isset($_POST['cargo_entrega']) ? trim($_POST['cargo_entrega']) : 'Responsable de Bienes'
            );
            
            $nombreArchivo = 'resguardo_' . $trabajador->getMatricula() . '_' . date('YmdHis') . '.pdf';
            break;

        case 'salida':
            $generador = new GeneradorSalidaPDF();
            
            // Validar campos requeridos para salida
            if (empty($_POST['area_origen'])) {
                throw new Exception('El campo "Área de Origen" es requerido para salidas');
            }
            if (empty($_POST['destino'])) {
                throw new Exception('El campo "Destino" es requerido para salidas');
            }
            if (empty($_POST['lugar_fecha_salida'])) {
                throw new Exception('El campo "Lugar y Fecha" es requerido para salidas');
            }
            
            // Mapear campos del formulario
            $datosAdicionales = array(
                'area_origen'       => trim($_POST['area_origen']),
                'destino'           => trim($_POST['destino']),
                'motivo'            => 'Para su ' . trim($_POST['destino']),
                'lugar_fecha'       => $_POST['lugar_fecha_salida'],
                'sujeto_devolucion' => isset($_POST['sujeto_devolucion']) ? strtolower(trim($_POST['sujeto_devolucion'])) : 'si',
                'fecha_devolucion'  => isset($_POST['fecha_devolucion']) ? trim($_POST['fecha_devolucion']) : '',
                'observaciones'     => isset($_POST['observaciones_salida']) ? trim($_POST['observaciones_salida']) : '',
                'responsable_recibe' => $trabajador->getNombre()
            );
            
            // Validar fecha de devolución si es requerida
            if ($datosAdicionales['sujeto_devolucion'] === 'si' && empty($datosAdicionales['fecha_devolucion'])) {
                logError('Advertencia: Salida marcada como sujeta a devolución pero sin fecha de devolución');
            }
            
            $nombreArchivo = 'salida_' . $trabajador->getMatricula() . '_' . date('YmdHis') . '.pdf';
            break;

        default:
            throw new Exception('Tipo de documento no válido');
    }

    $rutaPDF = $pdfDir . '/' . $nombreArchivo;

    logError('Iniciando generación del archivo PDF', [
        'nombre_archivo' => $nombreArchivo,
        'ruta' => $rutaPDF
    ]);

    // Generar el PDF
    $resultado = $generador->generar($trabajador, $bienes, $datosAdicionales, $rutaPDF);

    // Verificar que el PDF se generó correctamente
    if (!file_exists($rutaPDF)) {
        throw new Exception('Error al generar el archivo PDF. El archivo no se creó.');
    }

    $tamanoArchivo = filesize($rutaPDF);
    if ($tamanoArchivo === 0) {
        throw new Exception('El archivo PDF se generó pero está vacío');
    }

    logError('PDF generado exitosamente', [
        'nombre' => $nombreArchivo,
        'tamaño' => $tamanoArchivo . ' bytes'
    ]);

    // Copiar el PDF al directorio de outputs si existe
    $outputDir = '/mnt/user-data/outputs/';
    if (is_dir($outputDir) && is_writable($outputDir)) {
        $outputPath = $outputDir . $nombreArchivo;
        if (copy($rutaPDF, $outputPath)) {
            logError('PDF copiado a directorio de outputs', ['ruta' => $outputPath]);
        } else {
            logError('Advertencia: No se pudo copiar el PDF al directorio de outputs');
        }
    }

    // Establecer mensajes de éxito
    $_SESSION['mensaje'] = 'PDF generado exitosamente: ' . $nombreArchivo;
    $_SESSION['tipo_mensaje'] = 'success';
    $_SESSION['archivo_pdf'] = $nombreArchivo;
    $_SESSION['ruta_pdf'] = 'pdfs/' . $nombreArchivo; // Ruta relativa para descarga
    $_SESSION['tamano_pdf'] = round($tamanoArchivo / 1024, 2); // Tamaño en KB

    logError('Proceso completado exitosamente');

} catch (Exception $e) {
    logError("Error en procesar_pdf.php: " . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    $_SESSION['mensaje'] = 'Error al generar el PDF: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'error';
}

// Redirigir de vuelta al formulario
header('Location: generador_documentos.php');
exit;