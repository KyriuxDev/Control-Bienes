<?php
// public/api/exportar_excel.php - VERSIÓN CON SimpleXLSXGen
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Cargar SimpleXLSXGen manualmente (sin composer)
$simplexlsxPath = __DIR__ . '/../../lib/simplexlsxgen-master/src/SimpleXLSXGen.php';

if (!file_exists($simplexlsxPath)) {
    die("ERROR: No se encontró SimpleXLSXGen.php en: " . $simplexlsxPath . "<br>Verifique la ruta y que el archivo existe.");
}

require_once $simplexlsxPath;

// Verificar que la clase se cargó
if (!class_exists('Shuchkin\SimpleXLSXGen')) {
    die("ERROR: La clase SimpleXLSXGen no se cargó correctamente.<br>Contenido del archivo: " . (file_exists($simplexlsxPath) ? "Existe" : "No existe"));
}

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;

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
                <h1 class="text-xl font-bold">Error al Exportar</h1>
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

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // ============================================
    // PREPARAR DATOS
    // ============================================
    
    // 1. MOVIMIENTOS
    $stmt = $pdo->query("
        SELECT 
            m.id_movimiento as 'ID',
            m.folio as 'Folio',
            m.tipo_movimiento as 'Tipo',
            DATE_FORMAT(m.fecha, '%d/%m/%Y') as 'Fecha',
            m.lugar as 'Lugar',
            m.area as 'Área',
            t_recibe.nombre as 'Recibe',
            t_entrega.nombre as 'Entrega',
            COUNT(dm.id_bien) as 'Total Bienes'
        FROM movimiento m
        LEFT JOIN trabajador t_recibe ON m.matricula_recibe = t_recibe.matricula
        LEFT JOIN trabajador t_entrega ON m.matricula_entrega = t_entrega.matricula
        LEFT JOIN detalle_movimiento dm ON m.id_movimiento = dm.id_movimiento
        GROUP BY m.id_movimiento
        ORDER BY m.fecha DESC
    ");
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. BIENES
    $stmt = $pdo->query("
        SELECT 
            b.id_bien as 'ID',
            b.descripcion as 'Descripción',
            b.naturaleza as 'Naturaleza',
            b.marca as 'Marca',
            b.modelo as 'Modelo',
            b.serie as 'Serie',
            COUNT(dm.id_movimiento) as 'Veces Usado'
        FROM bien b
        LEFT JOIN detalle_movimiento dm ON b.id_bien = dm.id_bien
        GROUP BY b.id_bien
        ORDER BY COUNT(dm.id_movimiento) DESC
    ");
    $bienes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. TRABAJADORES
    $stmt = $pdo->query("
        SELECT 
            t.matricula as 'Matrícula',
            t.nombre as 'Nombre',
            t.cargo as 'Cargo',
            t.institucion as 'Institución',
            t.adscripcion as 'Adscripción',
            t.telefono as 'Teléfono',
            COUNT(DISTINCT m.id_movimiento) as 'Total Movimientos'
        FROM trabajador t
        LEFT JOIN movimiento m ON (t.matricula = m.matricula_recibe OR t.matricula = m.matricula_entrega)
        GROUP BY t.matricula
        ORDER BY COUNT(DISTINCT m.id_movimiento) DESC
    ");
    $trabajadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. ESTADÍSTICAS
    $totalMovimientos = count($movimientos);
    $totalBienes = count($bienes);
    $totalTrabajadores = count($trabajadores);
    
    // Estadísticas por tipo
    $stmt = $pdo->query("SELECT tipo_movimiento, COUNT(*) as total FROM movimiento GROUP BY tipo_movimiento");
    $tipoStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas por naturaleza
    $stmt = $pdo->query("SELECT naturaleza, COUNT(*) as total FROM bien GROUP BY naturaleza");
    $natStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas de préstamos activos (solo Prestamo, sin Constancia de salida)
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM movimiento 
        WHERE tipo_movimiento = 'Prestamo'
        AND dias_prestamo > 0
    ");
    $prestamosActivos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Estadísticas de devoluciones pendientes (Constancia de salida con fecha futura)
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM movimiento 
        WHERE fecha_devolucion IS NOT NULL 
        AND fecha_devolucion >= CURDATE()
    ");
    $devolucionesPendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Log para debugging
    error_log("Total movimientos: " . count($movimientos));
    error_log("Total bienes: " . count($bienes));
    error_log("Total trabajadores: " . count($trabajadores));
    error_log("Préstamos activos: " . $prestamosActivos);
    error_log("Devoluciones pendientes: " . $devolucionesPendientes);
    
    // Verificar que hay datos
    if (count($movimientos) === 0 && count($bienes) === 0 && count($trabajadores) === 0) {
        throw new Exception("No hay datos en la base de datos para exportar. Agregue movimientos, bienes o trabajadores primero.");
    }
    
    // ============================================
    // GENERAR EXCEL CON SIMPLEXLSXGEN
    // ============================================
    
    $sheets = [];
    
    // --------------------------------------------
    // HOJA 1: ESTADÍSTICAS GENERALES
    // --------------------------------------------
    $estadisticas = [];
    
    // Encabezado principal
    $estadisticas[] = ['ESTADÍSTICAS GENERALES DEL SISTEMA'];
    $estadisticas[] = ['']; // Fila vacía
    
    // Totales
    $estadisticas[] = ['Descripción', 'Valor'];
    $estadisticas[] = ['Total de Movimientos', $totalMovimientos];
    $estadisticas[] = ['Total de Bienes', $totalBienes];
    $estadisticas[] = ['Total de Trabajadores', $totalTrabajadores];
    $estadisticas[] = ['']; // Fila vacía
    
    // Estadísticas de préstamos y devoluciones
    $estadisticas[] = ['ESTADO DE PRÉSTAMOS Y DEVOLUCIONES'];
    $estadisticas[] = ['Estado', 'Cantidad'];
    $estadisticas[] = ['Préstamos Activos', $prestamosActivos];
    $estadisticas[] = ['Devoluciones Pendientes', $devolucionesPendientes];
    $estadisticas[] = ['']; // Fila vacía
    
    // Movimientos por tipo
    $estadisticas[] = ['MOVIMIENTOS POR TIPO'];
    $estadisticas[] = ['Tipo', 'Cantidad'];
    
    foreach ($tipoStats as $stat) {
        $estadisticas[] = [$stat['tipo_movimiento'], $stat['total']];
    }
    
    $estadisticas[] = ['']; // Fila vacía
    
    // Bienes por naturaleza
    $estadisticas[] = ['BIENES POR NATURALEZA'];
    $estadisticas[] = ['Naturaleza', 'Cantidad'];
    
    foreach ($natStats as $stat) {
        $estadisticas[] = [$stat['naturaleza'], $stat['total']];
    }
    
    $estadisticas[] = ['']; // Fila vacía
    $estadisticas[] = ['Fecha de Generación', date('d/m/Y')];
    
    $sheets[] = $estadisticas;
    
    // --------------------------------------------
    // HOJA 2: MOVIMIENTOS
    // --------------------------------------------
    $movimientosSheet = [];
    
    if (!empty($movimientos)) {
        // Obtener encabezados
        $headers = array_keys($movimientos[0]);
        $movimientosSheet[] = $headers;
        
        // Agregar datos
        foreach ($movimientos as $mov) {
            $movimientosSheet[] = array_values($mov);
        }
    } else {
        $movimientosSheet[] = ['No hay movimientos registrados'];
    }
    
    $sheets[] = $movimientosSheet;
    
    // --------------------------------------------
    // HOJA 3: BIENES
    // --------------------------------------------
    $bienesSheet = [];
    
    if (!empty($bienes)) {
        $headers = array_keys($bienes[0]);
        $bienesSheet[] = $headers;
        
        foreach ($bienes as $bien) {
            $bienesSheet[] = array_values($bien);
        }
    } else {
        $bienesSheet[] = ['No hay bienes registrados'];
    }
    
    $sheets[] = $bienesSheet;
    
    // --------------------------------------------
    // HOJA 4: TRABAJADORES
    // --------------------------------------------
    $trabajadoresSheet = [];
    
    if (!empty($trabajadores)) {
        $headers = array_keys($trabajadores[0]);
        $trabajadoresSheet[] = $headers;
        
        foreach ($trabajadores as $t) {
            $trabajadoresSheet[] = array_values($t);
        }
    } else {
        $trabajadoresSheet[] = ['No hay trabajadores registrados'];
    }
    
    $sheets[] = $trabajadoresSheet;
    
    // --------------------------------------------
    // HOJA 5: PRÉSTAMOS ACTIVOS (SIN fecha_devolucion)
    // --------------------------------------------
    $stmt = $pdo->query("
        SELECT 
            m.folio as 'Folio',
            m.tipo_movimiento as 'Tipo',
            DATE_FORMAT(m.fecha, '%d/%m/%Y') as 'Fecha Préstamo',
            m.dias_prestamo as 'Días de Préstamo',
            t_recibe.nombre as 'Responsable',
            t_recibe.cargo as 'Cargo',
            t_recibe.telefono as 'Teléfono',
            m.area as 'Área',
            m.lugar as 'Lugar',
            COUNT(dm.id_bien) as 'Total Bienes'
        FROM movimiento m
        LEFT JOIN trabajador t_recibe ON m.matricula_recibe = t_recibe.matricula
        LEFT JOIN detalle_movimiento dm ON m.id_movimiento = dm.id_movimiento
        WHERE m.tipo_movimiento = 'Prestamo'
        AND m.dias_prestamo > 0
        GROUP BY m.id_movimiento
        ORDER BY m.fecha DESC
    ");
    $prestamosAct = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $prestamosSheet = [];
    
    if (!empty($prestamosAct)) {
        $headers = array_keys($prestamosAct[0]);
        $prestamosSheet[] = $headers;
        
        foreach ($prestamosAct as $pres) {
            $prestamosSheet[] = array_values($pres);
        }
    } else {
        $prestamosSheet[] = ['No hay préstamos activos'];
    }
    
    $sheets[] = $prestamosSheet;
    
    // --------------------------------------------
    // HOJA 6: DEVOLUCIONES PENDIENTES
    // --------------------------------------------
    $stmt = $pdo->query("
        SELECT 
            m.folio as 'Folio',
            m.tipo_movimiento as 'Tipo',
            DATE_FORMAT(m.fecha, '%d/%m/%Y') as 'Fecha Movimiento',
            DATE_FORMAT(m.fecha_devolucion, '%d/%m/%Y') as 'Fecha Devolución',
            DATEDIFF(m.fecha_devolucion, CURDATE()) as 'Días Restantes',
            t_recibe.nombre as 'Responsable',
            t_recibe.cargo as 'Cargo',
            t_recibe.telefono as 'Teléfono',
            m.area as 'Área',
            COUNT(dm.id_bien) as 'Total Bienes'
        FROM movimiento m
        LEFT JOIN trabajador t_recibe ON m.matricula_recibe = t_recibe.matricula
        LEFT JOIN detalle_movimiento dm ON m.id_movimiento = dm.id_movimiento
        WHERE m.fecha_devolucion IS NOT NULL 
        AND m.fecha_devolucion >= CURDATE()
        GROUP BY m.id_movimiento
        ORDER BY m.fecha_devolucion ASC
    ");
    $devolucionesPend = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $devolucionesSheet = [];
    
    if (!empty($devolucionesPend)) {
        $headers = array_keys($devolucionesPend[0]);
        $devolucionesSheet[] = $headers;
        
        foreach ($devolucionesPend as $dev) {
            $devolucionesSheet[] = array_values($dev);
        }
    } else {
        $devolucionesSheet[] = ['No hay devoluciones pendientes'];
    }
    
    $sheets[] = $devolucionesSheet;
    
    // ============================================
    // GENERAR Y DESCARGAR ARCHIVO
    // ============================================
    
    $filename = 'Reporte_IMSS_' . date('Y-m-d_His') . '.xlsx';
    
    error_log("Generando archivo Excel: " . $filename);
    error_log("Total de hojas: " . count($sheets));
    
    try {
        // Crear el objeto Excel
        $xlsx = new Shuchkin\SimpleXLSXGen();
        
        // Agregar cada hoja
        $sheetNames = [
            'Estadísticas', 
            'Movimientos', 
            'Bienes', 
            'Trabajadores',
            'Préstamos Activos',
            'Devoluciones Pendientes'
        ];
        
        foreach ($sheets as $index => $sheetData) {
            if ($index === 0) {
                // Primera hoja
                $xlsx->addSheet($sheetData, $sheetNames[$index]);
            } else {
                // Hojas adicionales
                $xlsx->addSheet($sheetData, $sheetNames[$index]);
            }
        }
        
        // Limpiar buffer de salida
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Headers para descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        
        $xlsx->downloadAs($filename);
        
    } catch (Exception $e) {
        error_log("Error al generar Excel: " . $e->getMessage());
        throw new Exception("Error al generar el archivo Excel: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    error_log("ERROR en exportar_excel.php: " . $e->getMessage());
    mostrarError($e->getMessage());
}

exit;