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

    // Obtener datos del formulario
    $tipoDocumento = isset($_POST['tipo_documento']) ? $_POST['tipo_documento'] : '';
    $trabajadorId  = isset($_POST['trabajador_id']) ? $_POST['trabajador_id'] : '';

    // Validar datos básicos
    if (empty($tipoDocumento) || empty($trabajadorId)) {
        throw new Exception('Faltan datos obligatorios');
    }

    // Obtener datos del trabajador
    $trabajador = $trabajadorRepo->getById($trabajadorId);
    if (!$trabajador) {
        throw new Exception('Trabajador no encontrado');
    }

    // Procesar bienes
    $bienes = array();
    if (isset($_POST['bienes']) && is_array($_POST['bienes'])) {
        foreach ($_POST['bienes'] as $bienData) {
            if (!empty($bienData['bien_id'])) {
                $bien = $bienRepo->getById($bienData['bien_id']);
                if ($bien) {
                    $bienes[] = array(
                        'bien'     => $bien,
                        'cantidad' => isset($bienData['cantidad']) ? intval($bienData['cantidad']) : 1
                    );
                }
            }
        }
    }

    if (empty($bienes)) {
        throw new Exception('Debe agregar al menos un bien');
    }

    // Crear directorio de PDFs si no existe
    $pdfDir = __DIR__ . '/pdfs';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0755, true);
    }

    // Generar PDF según el tipo de documento
    $nombreArchivo = '';
    $rutaPDF       = '';

    switch ($tipoDocumento) {

        case 'prestamo':
            $generador = new GeneradorPrestamoPDF();
            
            // Mapear campos del formulario a lo que espera el generador
            $datosAdicionales = array(
                'fecha_emision'        => isset($_POST['fecha_emision_prestamo']) ? $_POST['fecha_emision_prestamo'] : date('Y-m-d'),
                'lugar_fecha'          => isset($_POST['lugar_fecha_prestamo']) ? $_POST['lugar_fecha_prestamo'] : date('d/m/Y'),
                'nota'                 => isset($_POST['nota_prestamo']) ? $_POST['nota_prestamo'] : '',
                'matricula_autoriza'   => isset($_POST['matricula_autoriza']) ? $_POST['matricula_autoriza'] : '',
                'matricula_recibe'     => isset($_POST['matricula_recibe']) ? $_POST['matricula_recibe'] : ''
            );
            
            $nombreArchivo = 'prestamo_' . $trabajador->getMatricula() . '_' . date('YmdHis') . '.pdf';
            $rutaPDF = $pdfDir . '/' . $nombreArchivo;
            
            $generador->generar($trabajador, $bienes, $datosAdicionales, $rutaPDF);
            break;

        case 'resguardo':
            $generador = new GeneradorResguardoPDF();
            
            // Mapear campos del formulario
            $datosAdicionales = array(
                'folio'        => isset($_POST['folio_resguardo']) ? $_POST['folio_resguardo'] : 'RES-' . date('YmdHis'),
                'lugar_fecha'  => isset($_POST['lugar_fecha_resguardo']) ? $_POST['lugar_fecha_resguardo'] : date('d/m/Y'),
                'notas'        => isset($_POST['notas_resguardo']) ? $_POST['notas_resguardo'] : ''
            );
            
            $nombreArchivo = 'resguardo_' . $trabajador->getMatricula() . '_' . date('YmdHis') . '.pdf';
            $rutaPDF = $pdfDir . '/' . $nombreArchivo;
            
            $generador->generar($trabajador, $bienes, $datosAdicionales, $rutaPDF);
            break;

        case 'salida':
            $generador = new GeneradorSalidaPDF();
            
            // Mapear campos del formulario
            $datosAdicionales = array(
                'area_origen'       => isset($_POST['area_origen']) ? $_POST['area_origen'] : '',
                'destino'           => isset($_POST['destino']) ? $_POST['destino'] : '',
                'motivo'            => isset($_POST['destino']) ? 'Para su ' . $_POST['destino'] : '',
                'lugar_fecha'       => isset($_POST['lugar_fecha_salida']) ? $_POST['lugar_fecha_salida'] : date('d/m/Y'),
                'sujeto_devolucion' => isset($_POST['sujeto_devolucion']) ? strtolower($_POST['sujeto_devolucion']) : 'si',
                'fecha_devolucion'  => isset($_POST['fecha_devolucion']) ? $_POST['fecha_devolucion'] : '',
                'observaciones'     => isset($_POST['observaciones_salida']) ? $_POST['observaciones_salida'] : ''
            );
            
            $nombreArchivo = 'salida_' . $trabajador->getMatricula() . '_' . date('YmdHis') . '.pdf';
            $rutaPDF = $pdfDir . '/' . $nombreArchivo;
            
            $generador->generar($trabajador, $bienes, $datosAdicionales, $rutaPDF);
            break;

        default:
            throw new Exception('Tipo de documento no válido');
    }

    // Verificar que el PDF se generó correctamente
    if (!file_exists($rutaPDF)) {
        throw new Exception('Error al generar el archivo PDF');
    }

    // Copiar el PDF al directorio de outputs si existe
    $outputPath = '/mnt/user-data/outputs/' . $nombreArchivo;
    if (is_dir('/mnt/user-data/outputs/')) {
        copy($rutaPDF, $outputPath);
    }

    $_SESSION['mensaje'] = 'PDF generado exitosamente: ' . $nombreArchivo;
    $_SESSION['tipo_mensaje'] = 'success';
    $_SESSION['archivo_pdf'] = $nombreArchivo;
    $_SESSION['ruta_pdf'] = 'pdfs/' . $nombreArchivo; // Ruta relativa para descarga

} catch (Exception $e) {
    error_log("Error en procesar_pdf.php: " . $e->getMessage());
    $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
    $_SESSION['tipo_mensaje'] = 'error';
}

// Redirigir de vuelta al formulario
header('Location: generador_documentos.php');
exit;