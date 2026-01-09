<?php
// public/index.php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLMovimientoRepository;

$db = Database::getInstance();
$pdo = $db->getConnection();

$movimientoRepo = new MySQLMovimientoRepository($pdo);

// Obtener estadísticas básicas
try {
    $totalMovimientos = count($movimientoRepo->obtenerTodos());
    
    // Contar préstamos activos (últimos 30 días)
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM movimiento 
        WHERE tipo_movimiento = 'Prestamo' 
        AND fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $prestamosActivos = $stmt->fetch()['total'];
    
    // Contar movimientos recientes
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM movimiento 
        WHERE DATE(fecha) = CURDATE()
    ");
    $movimientosHoy = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
    $totalMovimientos = 0;
    $prestamosActivos = 0;
    $movimientosHoy = 0;
}

require __DIR__ . '/public/layouts/head.php';
?>

<body class="bg-background-light dark:bg-background-dark text-text-main font-display antialiased">

<?php require __DIR__ . '/public/layouts/topnav.php'; ?>

<!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-4xl font-black text-imss-dark dark:text-white tracking-tight mb-2">
            Bienvenido al Sistema
        </h1>
        <p class="text-imss-gray dark:text-gray-400 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">calendar_today</span>
            <?php
                $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
                echo date('d') . " de " . $meses[date('n')-1] . " de " . date('Y');
            ?>
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Stat 1: Total Movimientos -->
        <div class="bg-white dark:bg-[#1e2a1e] p-6 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="bg-primary/10 p-3 rounded-lg">
                    <span class="material-symbols-outlined text-primary text-2xl">inventory_2</span>
                </div>
                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">
                    Activo
                </span>
            </div>
            <p class="text-imss-gray dark:text-gray-400 text-sm font-medium mb-1">Total de Documentos</p>
            <p class="text-3xl font-bold text-imss-dark dark:text-white">
                <?php echo number_format($totalMovimientos); ?>
            </p>
        </div>

        <!-- Stat 2: Préstamos Activos -->
        <div class="bg-white dark:bg-[#1e2a1e] p-6 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-blue-600"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg">
                    <span class="material-symbols-outlined text-blue-600 text-2xl">swap_horiz</span>
                </div>
                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">
                    Últimos 30 días
                </span>
            </div>
            <p class="text-imss-gray dark:text-gray-400 text-sm font-medium mb-1">Préstamos Activos</p>
            <p class="text-3xl font-bold text-imss-dark dark:text-white">
                <?php echo number_format($prestamosActivos); ?>
            </p>
        </div>

        <!-- Stat 3: Movimientos Hoy -->
        <div class="bg-white dark:bg-[#1e2a1e] p-6 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-orange-500"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="bg-orange-50 dark:bg-orange-900/30 p-3 rounded-lg">
                    <span class="material-symbols-outlined text-orange-500 text-2xl">today</span>
                </div>
                <span class="bg-orange-100 text-orange-700 text-xs font-bold px-2 py-1 rounded-full">
                    Hoy
                </span>
            </div>
            <p class="text-imss-gray dark:text-gray-400 text-sm font-medium mb-1">Movimientos del Día</p>
            <p class="text-3xl font-bold text-imss-dark dark:text-white">
                <?php echo number_format($movimientosHoy); ?>
            </p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-imss-dark dark:text-white mb-4">Acciones Rápidas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Action 1: Nuevo Documento -->
            <a href="/imss-control-bienes/public/generador_documentos.php" class="flex flex-col items-center justify-center gap-3 bg-white dark:bg-[#1e2a1e] hover:bg-primary/5 dark:hover:bg-primary/10 border border-imss-border dark:border-gray-800 hover:border-primary/50 rounded-xl p-6 transition-all group text-center h-40">
                <div class="bg-gray-100 dark:bg-gray-800 group-hover:bg-white dark:group-hover:bg-primary/20 p-3 rounded-full transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-primary text-3xl">post_add</span>
                </div>
                <div>
                    <span class="block text-imss-dark dark:text-white font-bold group-hover:text-primary transition-colors">Nuevo Documento</span>
                    <span class="text-xs text-imss-gray dark:text-gray-400">Crear resguardo/préstamo</span>
                </div>
            </a>

            <!-- Action 2: Buscar Documentos -->
            <a href="/imss-control-bienes/public/gestion_documentos.php" class="flex flex-col items-center justify-center gap-3 bg-white dark:bg-[#1e2a1e] hover:bg-primary/5 dark:hover:bg-primary/10 border border-imss-border dark:border-gray-800 hover:border-primary/50 rounded-xl p-6 transition-all group text-center h-40">
                <div class="bg-gray-100 dark:bg-gray-800 group-hover:bg-white dark:group-hover:bg-primary/20 p-3 rounded-full transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-primary text-3xl">search</span>
                </div>
                <div>
                    <span class="block text-imss-dark dark:text-white font-bold group-hover:text-primary transition-colors">Buscar Documentos</span>
                    <span class="text-xs text-imss-gray dark:text-gray-400">Consultar historial</span>
                </div>
            </a>

            <!-- Action 3: Gestión de Bienes -->
            <a href="/imss-control-bienes/public/gestion_bienes.php" class="flex flex-col items-center justify-center gap-3 bg-white dark:bg-[#1e2a1e] hover:bg-primary/5 dark:hover:bg-primary/10 border border-imss-border dark:border-gray-800 hover:border-primary/50 rounded-xl p-6 transition-all group text-center h-40">
                <div class="bg-gray-100 dark:bg-gray-800 group-hover:bg-white dark:group-hover:bg-primary/20 p-3 rounded-full transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-primary text-3xl">inventory</span>
                </div>
                <div>
                    <span class="block text-imss-dark dark:text-white font-bold group-hover:text-primary transition-colors">Gestión de Bienes</span>
                    <span class="text-xs text-imss-gray dark:text-gray-400">Administrar catálogo</span>
                </div>
            </a>

            <!-- Action 4: Reportes -->
            <a href="/imss-control-bienes/public/reportes.php" class="flex flex-col items-center justify-center gap-3 bg-white dark:bg-[#1e2a1e] hover:bg-primary/5 dark:hover:bg-primary/10 border border-imss-border dark:border-gray-800 hover:border-primary/50 rounded-xl p-6 transition-all group text-center h-40">
                <div class="bg-gray-100 dark:bg-gray-800 group-hover:bg-white dark:group-hover:bg-primary/20 p-3 rounded-full transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-primary text-3xl">bar_chart</span>
                </div>
                <div>
                    <span class="block text-imss-dark dark:text-white font-bold group-hover:text-primary transition-colors">Reportes</span>
                    <span class="text-xs text-imss-gray dark:text-gray-400">Información</span>
                </div>
            </a>
            

        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-[#1e2a1e] rounded-xl border border-imss-border dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">history</span>
                Movimientos Recientes
            </h3>
            <a href="/imss-control-bienes/public/gestion_documentos.php" class="text-xs font-semibold text-primary hover:underline">Ver todos</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/4 text-xs text-imss-gray dark:text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-3 font-semibold">Folio</th>
                        <th class="px-6 py-3 font-semibold">Tipo</th>
                        <th class="px-6 py-3 font-semibold">Responsable</th>
                        <th class="px-6 py-3 font-semibold">Fecha</th>
                        <!--<th class="px-6 py-3 font-semibold">Acciones</th>-->
                    </tr>
                </thead>
                <tbody class="divide-y divide-imss-border dark:divide-gray-800 text-sm">
                    <?php
                    // Obtener últimos 5 movimientos
                    $stmt = $pdo->query("
                        SELECT m.*, t.nombre as nombre_recibe 
                        FROM movimiento m
                        LEFT JOIN trabajador t ON m.matricula_recibe = t.matricula
                        ORDER BY m.fecha DESC 
                        LIMIT 5
                    ");
                    $movimientos = $stmt->fetchAll();
                    
                    if (empty($movimientos)) {
                        echo '<tr><td colspan="5" class="px-6 py-8 text-center text-imss-gray dark:text-gray-400">
                                <span class="material-symbols-outlined text-4xl mb-2 block">inbox</span>
                                No hay movimientos registrados
                              </td></tr>';
                    } else {
                        foreach ($movimientos as $mov) {
                            $tipoColor = 'gray';
                            if ($mov['tipo_movimiento'] == 'Prestamo') $tipoColor = 'blue';
                            if ($mov['tipo_movimiento'] == 'Resguardo') $tipoColor = 'green';
                            if ($mov['tipo_movimiento'] == 'Constancia de salida') $tipoColor = 'orange';
                            
                            echo '<tr class="hover:bg-gray-50 dark:hover:bg-white/4 transition-colors">';
                            echo '<td class="px-6 py-4 font-medium text-imss-dark dark:text-white">' . htmlspecialchars($mov['folio']) . '</td>';
                            echo '<td class="px-6 py-4">
                                    <span class="bg-' . $tipoColor . '-100 text-' . $tipoColor . '-800 text-xs px-2 py-1 rounded-full font-medium">
                                        ' . htmlspecialchars($mov['tipo_movimiento']) . '
                                    </span>
                                  </td>';
                            echo '<td class="px-6 py-4 text-imss-gray dark:text-gray-400">' . htmlspecialchars($mov['nombre_recibe']) . '</td>';
                            echo '<td class="px-6 py-4 text-imss-gray dark:text-gray-400">' . date('d/m/Y', strtotime($mov['fecha'])) . '</td>';
                            
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Footer -->
<footer class="bg-white dark:bg-[#1e2a1e] border-t border-imss-border dark:border-gray-800 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-imss-gray dark:text-gray-400">
                © <?php echo date('Y'); ?> IMSS - Sistema de Control de Bienes
            </p>
            <!--
            <div class="flex gap-4 text-xs text-imss-gray dark:text-gray-400">
                
                <a href="#" class="hover:text-primary transition-colors">Ayuda</a>
                <span>•</span>
                <a href="#" class="hover:text-primary transition-colors">Documentación</a>
                <span>•</span>
                <a href="#" class="hover:text-primary transition-colors">Contacto</a>
               
            </div>
             -->
        </div>
    </div>
</footer>

<?php require __DIR__ . '/public/layouts/scripts.php'; ?>

</body>
</html>