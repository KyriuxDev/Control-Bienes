<?php
// public/vista_previa_pdf.php - VERSIÓN MÚLTIPLES FORMATOS CON FOLIO AUTOMÁTICO
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en producción
ini_set('log_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/generadores/GeneradorResguardoPDF.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Infrastructure\Helper\FolioGenerator;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md">
            <h1 class="text-xl font-bold text-red-600 mb-4">Método no permitido</h1>
            <p class="text-gray-700">Solo se aceptan peticiones POST.</p>
            <button onclick="window.close()" class="mt-4 w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                Cerrar Ventana
            </button>
        </div>
    </body>
    </html>';
    exit;
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
    
    // GENERAR FOLIO TEMPORAL PARA VISTA PREVIA
    $folio = $folioGenerator->generarFolio() . ' (VISTA PREVIA)';
    
    // Para vista previa, solo mostramos el primer tipo seleccionado
    $tipoMovimiento = $_POST['tipos_movimiento'][0];
    
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

    // 2. Obtener Bienes
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
                'cantidad' => isset($item['cantidad']) ? $item['cantidad'] : 1
            );
        }
    }
    
    // Verificar que haya al menos un bien
    if (empty($bienesSeleccionados)) {
        throw new Exception("Debe seleccionar al menos un bien válido");
    }

    // 3. Preparar datos adicionales
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

    // 4. Generar PDF temporal
    $rutaTemporal = __DIR__ . '/pdfs/preview_' . time() . '_' . uniqid() . '.pdf';

    // Asegurar que existe el directorio
    if (!file_exists(__DIR__ . '/pdfs')) {
        if (!mkdir(__DIR__ . '/pdfs', 0775, true)) {
            throw new Exception("No se pudo crear el directorio de PDFs");
        }
    }

    $generador = new GeneradorResguardoPDF();
    $generador->generar($trabajadorRecibe, $bienesSeleccionados, $datosAdicionales, $rutaTemporal);

    // Verificar que se generó el archivo
    if (!file_exists($rutaTemporal)) {
        throw new Exception("Error al generar el PDF temporal");
    }

    // 5. Mostrar en el navegador (inline, no descarga)
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Vista_Previa_' . str_replace(' ', '_', $tipoMovimiento) . '.pdf"');
    header('Content-Length: ' . filesize($rutaTemporal));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($rutaTemporal);

    // 6. Limpiar archivo temporal
    @unlink($rutaTemporal);
    
} catch (Exception $e) {
    error_log("ERROR en vista_previa_pdf.php: " . $e->getMessage());
    error_log("POST data: " . print_r($_POST, true));
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Mostrar error al usuario
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
        <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
            <div class="flex items-center gap-3 text-red-600 mb-4">
                <svg class="w-8 h-8 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h1 class="text-xl font-bold">Error en Vista Previa</h1>
            </div>
            <div class="mb-4">
                <p class="text-gray-700 mb-2"><strong>Mensaje:</strong></p>
                <p class="text-gray-600 text-sm bg-gray-50 p-3 rounded">' . htmlspecialchars($e->getMessage()) . '</p>
            </div>
            <div class="flex gap-2">
                <button onclick="window.close()" class="flex-1 bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 transition">
                    Cerrar
                </button>
                <button onclick="window.history.back()" class="flex-1 bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition">
                    Volver
                </button>
            </div>
        </div>
    </body>
    </html>';
}

exit;
?>