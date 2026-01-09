<?php
// public/api/exportar_excel.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Crear nuevo Spreadsheet
    $spreadsheet = new Spreadsheet();
    
    // ====================
    // HOJA 1: MOVIMIENTOS
    // ====================
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Movimientos');
    
    // Encabezados
    $headers = ['ID', 'Folio', 'Tipo', 'Fecha', 'Lugar', 'Área', 'Recibe', 'Entrega', 'Total Bienes'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '247528']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
    ];
    $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
    
    // Datos
    $stmt = $pdo->query("
        SELECT 
            m.id_movimiento,
            m.folio,
            m.tipo_movimiento,
            m.fecha,
            m.lugar,
            m.area,
            t_recibe.nombre as recibe,
            t_entrega.nombre as entrega,
            COUNT(dm.id_bien) as total_bienes
        FROM movimiento m
        LEFT JOIN trabajador t_recibe ON m.matricula_recibe = t_recibe.matricula
        LEFT JOIN trabajador t_entrega ON m.matricula_entrega = t_entrega.matricula
        LEFT JOIN detalle_movimiento dm ON m.id_movimiento = dm.id_movimiento
        GROUP BY m.id_movimiento
        ORDER BY m.fecha DESC
    ");
    $movimientos = $stmt->fetchAll(PDO::FETCH_NUM);
    
    if (!empty($movimientos)) {
        $sheet->fromArray($movimientos, NULL, 'A2');
    }
    
    // Ajustar ancho de columnas
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // ====================
    // HOJA 2: BIENES
    // ====================
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('Bienes');
    
    $headers2 = ['ID', 'Descripción', 'Naturaleza', 'Marca', 'Modelo', 'Serie', 'Veces Usado'];
    $sheet2->fromArray($headers2, NULL, 'A1');
    $sheet2->getStyle('A1:G1')->applyFromArray($headerStyle);
    
    $stmt = $pdo->query("
        SELECT 
            b.id_bien,
            b.descripcion,
            b.naturaleza,
            b.marca,
            b.modelo,
            b.serie,
            COUNT(dm.id_movimiento) as veces_usado
        FROM bien b
        LEFT JOIN detalle_movimiento dm ON b.id_bien = dm.id_bien
        GROUP BY b.id_bien
        ORDER BY veces_usado DESC
    ");
    $bienes = $stmt->fetchAll(PDO::FETCH_NUM);
    
    if (!empty($bienes)) {
        $sheet2->fromArray($bienes, NULL, 'A2');
    }
    
    foreach (range('A', 'G') as $col) {
        $sheet2->getColumnDimension($col)->setAutoSize(true);
    }
    
    // ====================
    // HOJA 3: TRABAJADORES
    // ====================
    $sheet3 = $spreadsheet->createSheet();
    $sheet3->setTitle('Trabajadores');
    
    $headers3 = ['Matrícula', 'Nombre', 'Cargo', 'Institución', 'Adscripción', 'Teléfono', 'Total Movimientos'];
    $sheet3->fromArray($headers3, NULL, 'A1');
    $sheet3->getStyle('A1:G1')->applyFromArray($headerStyle);
    
    $stmt = $pdo->query("
        SELECT 
            t.matricula,
            t.nombre,
            t.cargo,
            t.institucion,
            t.adscripcion,
            t.telefono,
            COUNT(DISTINCT m.id_movimiento) as total_movimientos
        FROM trabajador t
        LEFT JOIN movimiento m ON (t.matricula = m.matricula_recibe OR t.matricula = m.matricula_entrega)
        GROUP BY t.matricula
        ORDER BY total_movimientos DESC
    ");
    $trabajadores = $stmt->fetchAll(PDO::FETCH_NUM);
    
    if (!empty($trabajadores)) {
        $sheet3->fromArray($trabajadores, NULL, 'A2');
    }
    
    foreach (range('A', 'G') as $col) {
        $sheet3->getColumnDimension($col)->setAutoSize(true);
    }
    
    // ====================
    // HOJA 4: ESTADÍSTICAS
    // ====================
    $sheet4 = $spreadsheet->createSheet();
    $sheet4->setTitle('Estadísticas');
    
    // Título
    $sheet4->setCellValue('A1', 'ESTADÍSTICAS GENERALES DEL SISTEMA');
    $sheet4->mergeCells('A1:B1');
    $sheet4->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet4->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    $row = 3;
    
    // Total de movimientos
    $totalMovimientos = count($movimientos);
    $sheet4->setCellValue("A{$row}", 'Total de Movimientos:');
    $sheet4->setCellValue("B{$row}", $totalMovimientos);
    $sheet4->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
    
    // Total de bienes
    $totalBienes = count($bienes);
    $sheet4->setCellValue("A{$row}", 'Total de Bienes:');
    $sheet4->setCellValue("B{$row}", $totalBienes);
    $sheet4->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
    
    // Total de trabajadores
    $totalTrabajadores = count($trabajadores);
    $sheet4->setCellValue("A{$row}", 'Total de Trabajadores:');
    $sheet4->setCellValue("B{$row}", $totalTrabajadores);
    $sheet4->getStyle("A{$row}")->getFont()->setBold(true);
    $row += 2;
    
    // Movimientos por tipo
    $sheet4->setCellValue("A{$row}", 'MOVIMIENTOS POR TIPO');
    $sheet4->mergeCells("A{$row}:B{$row}");
    $sheet4->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
    
    $stmt = $pdo->query("SELECT tipo_movimiento, COUNT(*) as total FROM movimiento GROUP BY tipo_movimiento");
    $tipoStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tipoStats as $stat) {
        $sheet4->setCellValue("A{$row}", $stat['tipo_movimiento'] . ':');
        $sheet4->setCellValue("B{$row}", $stat['total']);
        $row++;
    }
    
    $row++;
    
    // Bienes por naturaleza
    $sheet4->setCellValue("A{$row}", 'BIENES POR NATURALEZA');
    $sheet4->mergeCells("A{$row}:B{$row}");
    $sheet4->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
    
    $stmt = $pdo->query("SELECT naturaleza, COUNT(*) as total FROM bien GROUP BY naturaleza");
    $natStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($natStats as $stat) {
        $sheet4->setCellValue("A{$row}", $stat['naturaleza'] . ':');
        $sheet4->setCellValue("B{$row}", $stat['total']);
        $row++;
    }
    
    $sheet4->getColumnDimension('A')->setWidth(30);
    $sheet4->getColumnDimension('B')->setWidth(15);
    
    // Fecha de generación
    $row += 2;
    $sheet4->setCellValue("A{$row}", 'Fecha de Generación:');
    $sheet4->setCellValue("B{$row}", date('d/m/Y H:i:s'));
    $sheet4->getStyle("A{$row}")->getFont()->setItalic(true);
    
    // Establecer la primera hoja como activa
    $spreadsheet->setActiveSheetIndex(0);
    
    // Generar archivo
    $writer = new Xlsx($spreadsheet);
    
    $filename = 'Reporte_IMSS_' . date('Y-m-d_His') . '.xlsx';
    
    // Headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    
} catch (Exception $e) {
    error_log("ERROR en exportar_excel.php: " . $e->getMessage());
    
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
                <h1 class="text-xl font-bold">Error al Exportar</h1>
            </div>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                <p class="text-red-800 font-medium">' . htmlspecialchars($e->getMessage()) . '</p>
            </div>
            <button onclick="window.close()" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 font-semibold">
                Cerrar
            </button>
        </div>
    </body>
    </html>';
}

exit;