<?php
// public/vista_previa_pdf.php - VERSIÓN MÚLTIPLES FORMATOS CON VALIDACIÓN
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/generadores/GeneradorResguardoPDF.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$db = Database::getInstance();
$pdo = $db->getConnection();

$trabajadorRepo = new MySQLTrabajadorRepository($pdo);
$bienRepo = new MySQLBienRepository($pdo);

try {
    // Validar que haya tipos de movimiento seleccionados
    if (!isset($_POST['tipos_movimiento']) || empty($_POST['tipos_movimiento'])) {
        throw new Exception("Debe seleccionar al menos un tipo de documento");
    }
    
    // Validar folio obligatorio (para vista previa también)
    if (empty($_POST['folio'])) {
        throw new Exception("El folio es obligatorio");
    }
    
    // Para vista previa, solo mostramos el primer tipo seleccionado
    $tipoMovimiento = $_POST['tipos_movimiento'][0];
    
    // 1. Obtener Trabajadores
    $trabajadorRecibe = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_recibe']);
    $trabajadorEntrega = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_entrega']);

    if (!$trabajadorRecibe || !$trabajadorEntrega) {
        throw new Exception("Error: Trabajadores no encontrados");
    }

    // 2. Obtener Bienes
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
        throw new Exception("Debe seleccionar al menos un bien");
    }

    // 3. Preparar datos adicionales
    $datosAdicionales = array(
        'folio' => isset($_POST['folio']) ? $_POST['folio'] : '',
        'lugar_fecha' => $_POST['lugar'] . ', ' . date('d \d\e F \d\e Y', strtotime($_POST['fecha'])),
        'recibe_resguardo' => $trabajadorRecibe->getNombre(),
        'entrega_resguardo' => $trabajadorEntrega->getNombre(),
        'cargo_entrega' => $trabajadorEntrega->getCargo(),
        'tipo_documento' => $tipoMovimiento
    );

    // 4. Generar PDF temporal
    $rutaTemporal = __DIR__ . '/pdfs/preview_' . time() . '_' . uniqid() . '.pdf';

    // Asegurar que existe el directorio
    if (!file_exists(__DIR__ . '/pdfs')) {
        mkdir(__DIR__ . '/pdfs', 0775, true);
    }

    $generador = new GeneradorResguardoPDF();
    $generador->generar($trabajadorRecibe, $bienesSeleccionados, $datosAdicionales, $rutaTemporal);

    // 5. Mostrar en el navegador (inline, no descarga)
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Vista_Previa_' . str_replace(' ', '_', $tipoMovimiento) . '.pdf"');
    header('Content-Length: ' . filesize($rutaTemporal));
    readfile($rutaTemporal);

    // 6. Limpiar archivo temporal
    @unlink($rutaTemporal);
    
} catch (Exception $e) {
    error_log("ERROR en vista_previa_pdf.php: " . $e->getMessage());
    
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
                <h1 class="text-xl font-bold">Error en Vista Previa</h1>
            </div>
            <p class="text-gray-700 mb-4">' . htmlspecialchars($e->getMessage()) . '</p>
            <button onclick="window.close()" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                Cerrar Ventana
            </button>
        </div>
    </body>
    </html>';
}

exit;
?>