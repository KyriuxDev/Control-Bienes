<?php
// public/api/generar_reporte_custom.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use setasign\Fpdi\Tcpdf\Fpdi;

// Función para mostrar error
function mostrarError($mensaje) {
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
                <h1 class="text-xl font-bold">Error al Generar Reporte</h1>
            </div>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                <p class="text-red-800 font-medium">' . htmlspecialchars($mensaje) . '</p>
            </div>
            <button onclick="window.close()" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 font-semibold">
                Cerrar
            </button>
        </div>
    </body>
    </html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mostrarError('Método no permitido');
}

try {
    // Validar fechas
    if (empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin'])) {
        throw new Exception("Las fechas de inicio y fin son obligatorias");
    }
    
    $fechaInicio = $_POST['fecha_inicio'];
    $fechaFin = $_POST['fecha_fin'];
    $tipoMovimiento = isset($_POST['tipo_movimiento']) && !empty($_POST['tipo_movimiento']) ? $_POST['tipo_movimiento'] : null;
    
    // Validar que la fecha de inicio sea menor o igual a la fecha de fin
    if (strtotime($fechaInicio) > strtotime($fechaFin)) {
        throw new Exception("La fecha de inicio debe ser menor o igual a la fecha de fin");
    }
    
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Construir consulta SQL
    $sql = "SELECT 
                m.folio,
                m.tipo_movimiento,
                m.fecha,
                m.lugar,
                m.area,
                t_recibe.nombre as recibe_nombre,
                t_recibe.matricula as recibe_matricula,
                t_entrega.nombre as entrega_nombre,
                t_entrega.matricula as entrega_matricula,
                COUNT(dm.id_bien) as total_bienes
            FROM movimiento m
            LEFT JOIN trabajador t_recibe ON m.matricula_recibe = t_recibe.matricula
            LEFT JOIN trabajador t_entrega ON m.matricula_entrega = t_entrega.matricula
            LEFT JOIN detalle_movimiento dm ON m.id_movimiento = dm.id_movimiento
            WHERE DATE(m.fecha) BETWEEN :fecha_inicio AND :fecha_fin";
    
    $params = [
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => $fechaFin
    ];
    
    if ($tipoMovimiento) {
        $sql .= " AND m.tipo_movimiento = :tipo_movimiento";
        $params['tipo_movimiento'] = $tipoMovimiento;
    }
    
    $sql .= " GROUP BY m.id_movimiento ORDER BY m.fecha DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Crear PDF
    $pdf = new Fpdi();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();
    
    // Encabezado
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'REPORTE DE MOVIMIENTOS', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'INSTITUTO MEXICANO DEL SEGURO SOCIAL', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Información del reporte
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 6, 'Período:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)), 0, 1);
    
    if ($tipoMovimiento) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 6, 'Tipo Documento:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $tipoMovimiento, 0, 1);
    }
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 6, 'Total Movimientos:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, count($movimientos), 0, 1);
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 6, 'Fecha Generación:', 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, date('d/m/Y'), 0, 1);
    
    $pdf->Ln(5);
    
    // Tabla de movimientos
    if (empty($movimientos)) {
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'No se encontraron movimientos en el período seleccionado', 0, 1, 'C');
    } else {
        // Encabezados de tabla
        $pdf->SetFillColor(36, 117, 40);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 8);
        
        $pdf->Cell(20, 7, 'FOLIO', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'TIPO', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'FECHA', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, 'RECIBE', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, 'ENTREGA', 1, 0, 'C', 1);
        $pdf->Cell(15, 7, 'BIENES', 1, 1, 'C', 1);
        
        // Datos
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 7);
        
        foreach ($movimientos as $mov) {
            $pdf->Cell(20, 6, $mov['folio'], 1, 0, 'C');
            
            // Tipo con color
            $tipoColor = [240, 240, 240];
            if ($mov['tipo_movimiento'] === 'Resguardo') $tipoColor = [134, 239, 172];
            if ($mov['tipo_movimiento'] === 'Prestamo') $tipoColor = [219, 234, 254];
            if ($mov['tipo_movimiento'] === 'Constancia de salida') $tipoColor = [254, 243, 199];
            
            $pdf->SetFillColor($tipoColor[0], $tipoColor[1], $tipoColor[2]);
            $pdf->Cell(25, 6, $mov['tipo_movimiento'], 1, 0, 'C', 1);
            $pdf->SetFillColor(255, 255, 255);
            
            $pdf->Cell(25, 6, date('d/m/Y', strtotime($mov['fecha'])), 1, 0, 'C');
            $pdf->Cell(50, 6, substr($mov['recibe_nombre'], 0, 50), 1, 0, 'L');
            $pdf->Cell(50, 6, substr($mov['entrega_nombre'], 0, 50), 1, 0, 'L');
            $pdf->Cell(15, 6, $mov['total_bienes'], 1, 1, 'C');
        }
        
        // Resumen estadístico
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'RESUMEN ESTADÍSTICO', 0, 1);
        $pdf->SetFont('helvetica', '', 9);
        
        // Contar por tipo
        $resumen = [];
        foreach ($movimientos as $mov) {
            $tipo = $mov['tipo_movimiento'];
            if (!isset($resumen[$tipo])) {
                $resumen[$tipo] = 0;
            }
            $resumen[$tipo]++;
        }
        
        foreach ($resumen as $tipo => $cantidad) {
            $pdf->Cell(60, 6, $tipo . ':', 0, 0);
            $pdf->Cell(0, 6, $cantidad . ' movimiento(s)', 0, 1);
        }
        
        // Total de bienes involucrados
        $totalBienes = array_sum(array_column($movimientos, 'total_bienes'));
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(60, 6, 'Total de bienes involucrados:', 0, 0);
        $pdf->Cell(0, 6, $totalBienes, 0, 1);
    }
    
    // Output
    $nombreArchivo = 'Reporte_' . date('Y-m-d_His') . '.pdf';
    $pdf->Output($nombreArchivo, 'I');
    
} catch (Exception $e) {
    error_log("ERROR en generar_reporte_custom.php: " . $e->getMessage());
    mostrarError($e->getMessage());
}

exit;