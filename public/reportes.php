<?php
// public/reportes.php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLMovimientoRepository;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLDetalleMovimientoRepository;

$db = Database::getInstance();
$pdo = $db->getConnection();

$movimientoRepo = new MySQLMovimientoRepository($pdo);
$bienRepo = new MySQLBienRepository($pdo);
$trabajadorRepo = new MySQLTrabajadorRepository($pdo);
$detalleRepo = new MySQLDetalleMovimientoRepository($pdo);

// Obtener estadísticas generales
$totalMovimientos = count($movimientoRepo->obtenerTodos());
$totalBienes = count($bienRepo->obtenerTodos());
$totalTrabajadores = count($trabajadorRepo->obtenerTodos());

// Estadísticas por tipo de movimiento
$stmt = $pdo->query("
    SELECT tipo_movimiento, COUNT(*) as total 
    FROM movimiento 
    GROUP BY tipo_movimiento
");
$movimientosPorTipo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas por naturaleza de bien
$stmt = $pdo->query("
    SELECT naturaleza, COUNT(*) as total 
    FROM bien 
    GROUP BY naturaleza
");
$bienesPorNaturaleza = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Movimientos por mes (últimos 12 meses)
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(fecha, '%Y-%m') as mes,
        COUNT(*) as total
    FROM movimiento
    WHERE fecha >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(fecha, '%Y-%m')
    ORDER BY mes DESC
");
$movimientosPorMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top 10 trabajadores con más movimientos
$stmt = $pdo->query("
    SELECT 
        t.nombre,
        t.matricula,
        COUNT(DISTINCT m.id_movimiento) as total_movimientos
    FROM trabajador t
    LEFT JOIN movimiento m ON (t.matricula = m.matricula_recibe OR t.matricula = m.matricula_entrega)
    GROUP BY t.matricula, t.nombre
    ORDER BY total_movimientos DESC
    LIMIT 10
");
$topTrabajadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Bienes más utilizados
$stmt = $pdo->query("
    SELECT 
        b.descripcion,
        b.naturaleza,
        COUNT(dm.id_movimiento) as veces_utilizado
    FROM bien b
    LEFT JOIN detalle_movimiento dm ON b.id_bien = dm.id_bien
    GROUP BY b.id_bien
    ORDER BY veces_utilizado DESC
    LIMIT 10
");
$bienesPopulares = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/layouts/head.php';
?>

<body class="bg-background-light dark:bg-background-dark min-h-screen text-text-main dark:text-white flex flex-col">

<?php require __DIR__ . '/layouts/topnav.php'; ?>

<!-- Main Content -->
<main class="flex-1 w-full max-w-[1440px] mx-auto p-6 md:p-10 flex flex-col gap-6">
    
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-sm text-imss-gray dark:text-gray-400">
        <a class="hover:text-primary flex items-center gap-1" href="/imss-control-bienes/index.php">
            <span class="material-symbols-outlined text-[18px]">home</span>
            Inicio
        </a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="font-medium text-primary">Reportes y Estadísticas</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 pb-2 border-b border-imss-border dark:border-gray-800">
        <div class="space-y-1">
            <h2 class="text-3xl font-bold text-imss-dark dark:text-white tracking-tight">Reportes y Estadísticas</h2>
            <p class="text-imss-gray dark:text-gray-400 text-base">Análisis detallado del sistema de control de bienes</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="exportarExcel()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">download</span>
                Exportar Excel
            </button>
        </div>
    </div>

    <!-- Reportes Personalizados -->
    <div class="bg-white dark:bg-[#1e2a1e] rounded-xl border border-imss-border dark:border-gray-800 shadow-sm p-6">
        <h3 class="text-lg font-bold text-imss-dark dark:text-white mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">filter_alt</span>
            Generar Reporte Personalizado
        </h3>
        
        <form action="api/generar_reporte_custom.php" method="POST" target="_blank" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Tipo de Movimiento</label>
                    <select name="tipo_movimiento" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        <option value="">Todos</option>
                        <option value="Resguardo">Resguardo</option>
                        <option value="Prestamo">Préstamo</option>
                        <option value="Constancia de salida">Constancia de Salida</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="w-full md:w-auto px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">description</span>
                Generar Reporte PDF
            </button>
        </form>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-[#1e2a1e] p-6 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center size-12 rounded-lg bg-primary/10 text-primary">
                    <span class="material-symbols-outlined text-2xl">description</span>
                </div>
                <span class="text-xs font-bold text-imss-gray dark:text-gray-400 uppercase">Total</span>
            </div>
            <p class="text-3xl font-bold text-imss-dark dark:text-white mb-1"><?php echo number_format($totalMovimientos); ?></p>
            <p class="text-sm text-imss-gray dark:text-gray-400">Documentos Generados</p>
        </div>

        <div class="bg-white dark:bg-[#1e2a1e] p-6 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center size-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600">
                    <span class="material-symbols-outlined text-2xl">inventory_2</span>
                </div>
                <span class="text-xs font-bold text-imss-gray dark:text-gray-400 uppercase">Catálogo</span>
            </div>
            <p class="text-3xl font-bold text-imss-dark dark:text-white mb-1"><?php echo number_format($totalBienes); ?></p>
            <p class="text-sm text-imss-gray dark:text-gray-400">Bienes Registrados</p>
        </div>

        <div class="bg-white dark:bg-[#1e2a1e] p-6 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center size-12 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600">
                    <span class="material-symbols-outlined text-2xl">group</span>
                </div>
                <span class="text-xs font-bold text-imss-gray dark:text-gray-400 uppercase">Personal</span>
            </div>
            <p class="text-3xl font-bold text-imss-dark dark:text-white mb-1"><?php echo number_format($totalTrabajadores); ?></p>
            <p class="text-sm text-imss-gray dark:text-gray-400">Trabajadores Activos</p>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Movimientos por Tipo -->
        <div class="bg-white dark:bg-[#1e2a1e] rounded-xl border border-imss-border dark:border-gray-800 shadow-sm p-6">
            <h3 class="text-lg font-bold text-imss-dark dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">pie_chart</span>
                Movimientos por Tipo
            </h3>
            <canvas id="chartTipoMovimiento"></canvas>
        </div>

        <!-- Bienes por Naturaleza -->
        <div class="bg-white dark:bg-[#1e2a1e] rounded-xl border border-imss-border dark:border-gray-800 shadow-sm p-6">
            <h3 class="text-lg font-bold text-imss-dark dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">donut_large</span>
                Bienes por Naturaleza
            </h3>
            <canvas id="chartNaturaleza"></canvas>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 gap-6">
        <!-- Movimientos por Mes -->
        <div class="bg-white dark:bg-[#1e2a1e] rounded-xl border border-imss-border dark:border-gray-800 shadow-sm p-6">
            <h3 class="text-lg font-bold text-imss-dark dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">show_chart</span>
                Tendencia de Movimientos (Últimos 12 Meses)
            </h3>
            <canvas id="chartMensual"></canvas>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Trabajadores -->
        <div class="bg-white dark:bg-[#1e2a1e] rounded-xl border border-imss-border dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 bg-gray-50/50 dark:bg-white/5">
                <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">leaderboard</span>
                    Top 10 Trabajadores Activos
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-white/4 text-xs text-imss-gray dark:text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 font-semibold">#</th>
                            <th class="px-6 py-3 font-semibold">Trabajador</th>
                            <th class="px-6 py-3 font-semibold text-right">Movimientos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-imss-border dark:divide-gray-800 text-sm">
                        <?php foreach ($topTrabajadores as $index => $t): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/4 transition-colors">
                                <td class="px-6 py-4 text-imss-gray dark:text-gray-400"><?php echo $index + 1; ?></td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-imss-dark dark:text-white"><?php echo htmlspecialchars($t['nombre']); ?></p>
                                    <p class="text-xs text-imss-gray dark:text-gray-400">Mat: <?php echo htmlspecialchars($t['matricula']); ?></p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                        <?php echo $t['total_movimientos']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bienes Populares -->
        <div class="bg-white dark:bg-[#1e2a1e] rounded-xl border border-imss-border dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 bg-gray-50/50 dark:bg-white/5">
                <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">trending_up</span>
                    Top 10 Bienes Más Utilizados
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-white/4 text-xs text-imss-gray dark:text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 font-semibold">#</th>
                            <th class="px-6 py-3 font-semibold">Bien</th>
                            <th class="px-6 py-3 font-semibold text-right">Usos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-imss-border dark:divide-gray-800 text-sm">
                        <?php foreach ($bienesPopulares as $index => $b): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/4 transition-colors">
                                <td class="px-6 py-4 text-imss-gray dark:text-gray-400"><?php echo $index + 1; ?></td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-imss-dark dark:text-white"><?php echo htmlspecialchars($b['descripcion']); ?></p>
                                    <p class="text-xs text-imss-gray dark:text-gray-400"><?php echo htmlspecialchars($b['naturaleza']); ?></p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                        <?php echo $b['veces_utilizado']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    

</main>

<footer class="bg-white dark:bg-[#1e2a1e] border-t border-imss-border dark:border-gray-800 mt-auto print:hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-imss-gray dark:text-gray-400">
                © <?php echo date('Y'); ?> IMSS - Sistema de Control de Bienes
            </p>
        </div>
    </div>
</footer>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
// Datos para gráficas
const movimientosPorTipo = <?php echo json_encode($movimientosPorTipo); ?>;
const bienesPorNaturaleza = <?php echo json_encode($bienesPorNaturaleza); ?>;
const movimientosPorMes = <?php echo json_encode(array_reverse($movimientosPorMes)); ?>;

// Gráfica de Movimientos por Tipo
const ctxTipo = document.getElementById('chartTipoMovimiento').getContext('2d');
new Chart(ctxTipo, {
    type: 'doughnut',
    data: {
        labels: movimientosPorTipo.map(m => m.tipo_movimiento),
        datasets: [{
            data: movimientosPorTipo.map(m => m.total),
            backgroundColor: [
                'rgba(36, 117, 40, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(168, 85, 247, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfica de Bienes por Naturaleza
const ctxNat = document.getElementById('chartNaturaleza').getContext('2d');
new Chart(ctxNat, {
    type: 'pie',
    data: {
        labels: bienesPorNaturaleza.map(b => b.naturaleza),
        datasets: [{
            data: bienesPorNaturaleza.map(b => b.total),
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(168, 85, 247, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfica Mensual
const ctxMensual = document.getElementById('chartMensual').getContext('2d');
new Chart(ctxMensual, {
    type: 'line',
    data: {
        labels: movimientosPorMes.map(m => {
            const [year, month] = m.mes.split('-');
            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            return meses[parseInt(month) - 1] + ' ' + year;
        }),
        datasets: [{
            label: 'Movimientos',
            data: movimientosPorMes.map(m => m.total),
            borderColor: 'rgba(36, 117, 40, 1)',
            backgroundColor: 'rgba(36, 117, 40, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Función para exportar a Excel
function exportarExcel() {
    window.location.href = 'api/exportar_excel.php';
}
</script>

<?php require __DIR__ . '/layouts/scripts.php'; ?>

<style>
@media print {
    body {
        background: white;
    }
    .print\:hidden {
        display: none !important;
    }
}
</style>

</body>
</html>