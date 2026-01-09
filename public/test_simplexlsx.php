<?php
// public/api/test_export_simple.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$simplexlsxPath = __DIR__ . '/../../lib/simplexlsxgen-master/src/SimpleXLSXGen.php';
require_once $simplexlsxPath;

// Datos de prueba muy simples
$data = [
    'Hoja1' => [
        [['Nombre', 'bold'], ['Edad', 'bold']],
        ['Juan', 25],
        ['María', 30],
        ['Pedro', 28]
    ]
];

$filename = 'Prueba_' . date('Y-m-d_His') . '.xlsx';

try {
    $xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
    
    // Limpiar buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Headers
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $xlsx->download();
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}