<?php
// public/vista_previa_pdf.php
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

// 1. Obtener Trabajador
$trabajador = $trabajadorRepo->obtenerPorMatricula($_POST['matricula']);

// 2. Obtener Bienes
$bienesSeleccionados = array();
foreach ($_POST['bienes'] as $item) {
    $bienObj = $bienRepo->obtenerPorId($item['bien_id']);
    if ($bienObj) {
        $bienesSeleccionados[] = array(
            'bien' => $bienObj,
            'cantidad' => $item['cantidad']
        );
    }
}

// 3. Preparar datos adicionales
$datosAdicionales = array(
    'folio' => $_POST['folio_resguardo'],
    'lugar_fecha' => $_POST['lugar_fecha_resguardo'],
    'recibe_resguardo' => $_POST['recibe_resguardo'],
    'entrega_resguardo' => $_POST['entrega_resguardo']
);

// 4. Generar PDF temporal
$rutaTemporal = __DIR__ . '/pdfs/preview_' . time() . '.pdf';
$generador = new GeneradorResguardoPDF();
$generador->generar($trabajador, $bienesSeleccionados, $datosAdicionales, $rutaTemporal);

// 5. Mostrar en el navegador (inline, no descarga)
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="Vista_Previa_Resguardo.pdf"');
header('Content-Length: ' . filesize($rutaTemporal));
readfile($rutaTemporal);

// 6. Limpiar archivo temporal después de un tiempo
// (opcional: puedes usar un cron job para limpiar archivos antiguos)
unlink($rutaTemporal);
exit;