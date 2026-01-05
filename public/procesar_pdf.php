<?php
// public/procesar_pdf.php
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
$trabajador = $trabajadorRepo->getById($_POST['trabajador_id']);

// 2. Obtener Bienes
$bienesSeleccionados = array();
foreach ($_POST['bienes'] as $item) {
    $bienObj = $bienRepo->getById($item['bien_id']);
    if ($bienObj) {
        $bienesSeleccionados[] = array(
            'bien' => $bienObj,
            'cantidad' => $item['cantidad']
        );
    }
}

// 3. Preparar datos adicionales para Resguardo
$datosAdicionales = array(
    'folio' => $_POST['folio_resguardo'],
    'lugar_fecha' => $_POST['lugar_fecha_resguardo'],
    'recibe_resguardo' => $_POST['recibe_resguardo'], // Quien entrega
    'entrega_resguardo' => $_POST['entrega_resguardo'] // Su cargo
);

// 4. Generar
$rutaSalida = __DIR__ . '/pdfs/resguardo_' . time() . '.pdf';
$generador = new GeneradorResguardoPDF();
$generador->generar($trabajador, $bienesSeleccionados, $datosAdicionales, $rutaSalida);

// Forzar descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Resguardo_Bienes.pdf"');
readfile($rutaSalida);
unlink($rutaSalida); // Borrar temporal
exit;