<?php
// public/vista_previa_pdf.php - ACTUALIZADO PARA NUEVA BASE DE DATOS
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

// 1. Obtener Trabajadores
$trabajadorRecibe = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_recibe']);
$trabajadorEntrega = $trabajadorRepo->obtenerPorMatricula($_POST['matricula_entrega']);

if (!$trabajadorRecibe || !$trabajadorEntrega) {
    die("Error: Trabajadores no encontrados");
}

// 2. Obtener Bienes
$bienesSeleccionados = array();
foreach ($_POST['bienes'] as $item) {
    $bienObj = $bienRepo->obtenerPorId($item['id_bien']);
    if ($bienObj) {
        $bienesSeleccionados[] = array(
            'bien' => $bienObj,
            'cantidad' => isset($item['cantidad']) ? $item['cantidad'] : 1
        );
    }
}

// 3. Preparar datos adicionales
$datosAdicionales = array(
    'folio' => isset($_POST['folio']) ? $_POST['folio'] : '',
    'lugar_fecha' => $_POST['lugar'] . ', ' . date('d \d\e F \d\e Y', strtotime($_POST['fecha'])),
    'recibe_resguardo' => $trabajadorEntrega->getNombre(), // Quien entrega
    'entrega_resguardo' => $trabajadorEntrega->getCargo(), // Su cargo
    'tipo_documento' => $_POST['tipo_movimiento']
);

// 4. Generar PDF temporal (usando el trabajador que recibe)
$rutaTemporal = __DIR__ . '/pdfs/preview_' . time() . '.pdf';

// Asegurar que existe el directorio
if (!file_exists(__DIR__ . '/pdfs')) {
    mkdir(__DIR__ . '/pdfs', 0775, true);
}

$generador = new GeneradorResguardoPDF();
$generador->generar($trabajadorRecibe, $bienesSeleccionados, $datosAdicionales, $rutaTemporal);

// 5. Mostrar en el navegador (inline, no descarga)
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="Vista_Previa_' . str_replace(' ', '_', $_POST['tipo_movimiento']) . '.pdf"');
header('Content-Length: ' . filesize($rutaTemporal));
readfile($rutaTemporal);

// 6. Limpiar archivo temporal
unlink($rutaTemporal);
exit;