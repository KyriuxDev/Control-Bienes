<?php
// public/gestion_documentos.php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLMovimientoRepository;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLDetalleMovimientoRepository;
use App\Infrastructure\Repository\MySQLBienRepository;

$db = Database::getInstance();
$pdo = $db->getConnection();

$movimientoRepo = new MySQLMovimientoRepository($pdo);
$trabajadorRepo = new MySQLTrabajadorRepository($pdo);
$detalleRepo = new MySQLDetalleMovimientoRepository($pdo);
$bienRepo = new MySQLBienRepository($pdo);

// Obtener todos los movimientos con información adicional
$movimientos = $movimientoRepo->obtenerTodos();

// Estadísticas básicas
$totalDocumentos = count($movimientos);
$resguardos = 0;
$prestamos = 0;
$constancias = 0;

foreach ($movimientos as $m) {
    $tipo = $m->getTipoMovimiento();
    
    if ($tipo === 'Resguardo') {
        $resguardos++;
    } elseif ($tipo === 'Prestamo') {
        $prestamos++;
    } elseif ($tipo === 'Constancia de salida') {
        $constancias++;
    }
}

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
        <span class="font-medium text-primary">Gestión de Documentos</span>
    </div>

    <!-- Header & Actions -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 pb-2 border-b border-imss-border dark:border-gray-800">
        <div class="space-y-1">
            <h2 class="text-3xl font-bold text-imss-dark dark:text-white tracking-tight">Catálogo de Documentos</h2>
            <p class="text-imss-gray dark:text-gray-400 text-base">Gestione y consulte todos los movimientos registrados en el sistema.</p>
        </div>
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <button onclick="exportarDocumentos()" class="flex-1 lg:flex-none h-10 px-4 bg-white dark:bg-surface-dark border border-imss-border dark:border-gray-800 text-imss-dark dark:text-white text-sm font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-center gap-2 transition-all">
                <span class="material-symbols-outlined">download</span>
                Exportar
            </button>
            <a href="generador_documentos.php" class="flex-1 lg:flex-none h-10 px-5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-dark flex items-center justify-center gap-2 shadow-sm transition-all hover:shadow-md">
                <span class="material-symbols-outlined">add</span>
                Nuevo Documento
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-[#1e2a1e] p-4 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary">
                    <span class="material-symbols-outlined">description</span>
                </div>
                <div>
                    <p class="text-xs text-imss-gray dark:text-gray-400 font-medium">Total</p>
                    <p class="text-2xl font-bold text-imss-dark dark:text-white"><?php echo $totalDocumentos; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e2a1e] p-4 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600">
                    <span class="material-symbols-outlined">shield_person</span>
                </div>
                <div>
                    <p class="text-xs text-imss-gray dark:text-gray-400 font-medium">Resguardos</p>
                    <p class="text-2xl font-bold text-imss-dark dark:text-white"><?php echo $resguardos; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e2a1e] p-4 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600">
                    <span class="material-symbols-outlined">swap_horiz</span>
                </div>
                <div>
                    <p class="text-xs text-imss-gray dark:text-gray-400 font-medium">Préstamos</p>
                    <p class="text-2xl font-bold text-imss-dark dark:text-white"><?php echo $prestamos; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e2a1e] p-4 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-600">
                    <span class="material-symbols-outlined">logout</span>
                </div>
                <div>
                    <p class="text-xs text-imss-gray dark:text-gray-400 font-medium">Constancias</p>
                    <p class="text-2xl font-bold text-imss-dark dark:text-white"><?php echo $constancias; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 p-5 space-y-5">
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-imss-gray">search</span>
            </div>
            <input 
                id="search-input"
                class="block w-full pl-11 pr-4 py-3.5 bg-background-light dark:bg-background-dark border border-imss-border dark:border-gray-800 rounded-lg text-imss-dark dark:text-white placeholder-imss-gray focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow sm:text-sm" 
                placeholder="Buscar por folio, trabajador o área..."
                type="text"
                onkeyup="filtrarDocumentos()"/>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative group">
                <label class="block text-xs font-semibold text-imss-gray dark:text-gray-400 mb-1.5 uppercase tracking-wide">Tipo de Documento</label>
                <div class="relative">
                    <select id="filter-tipo" onchange="filtrarDocumentos()" class="appearance-none w-full bg-white dark:bg-[#1e2a1e] border border-imss-border dark:border-gray-800 text-imss-dark dark:text-white text-sm rounded-lg p-2.5 pr-8 focus:ring-1 focus:ring-primary outline-none cursor-pointer">
                        <option value="">Todos los tipos</option>
                        <option value="Resguardo">Resguardo</option>
                        <option value="Prestamo">Préstamo</option>
                        <option value="Constancia de salida">Constancia de Salida</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-imss-gray">
                        <span class="material-symbols-outlined">expand_more</span>
                    </div>
                </div>
            </div>

            <div class="flex items-end">
                <button onclick="limpiarFiltros()" class="w-full h-[42px] px-4 bg-gray-100 dark:bg-gray-800 border border-imss-border dark:border-gray-700 text-imss-dark dark:text-white text-sm font-semibold rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 flex items-center justify-center gap-2 transition-all">
                    <span class="material-symbols-outlined">filter_alt_off</span>
                    Limpiar Filtros
                </button>
            </div>
        </div>

        <div id="results-counter" class="text-sm text-imss-gray dark:text-gray-400">
            Mostrando <span id="visible-count"><?php echo $totalDocumentos; ?></span> de <span id="total-count"><?php echo $totalDocumentos; ?></span> documentos
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 overflow-hidden flex flex-col flex-1">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="bg-background-light dark:bg-background-dark border-b border-imss-border dark:border-gray-800">
                    <tr>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider">Folio</th>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Responsable</th>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Fecha</th>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">Área</th>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y divide-imss-border dark:divide-gray-800">
                    <?php if (empty($movimientos)): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center">
                                <div class="flex flex-col items-center gap-3 text-imss-gray dark:text-gray-400">
                                    <span class="material-symbols-outlined text-5xl">inbox</span>
                                    <p class="font-medium">No hay documentos registrados</p>
                                    <a href="generador_documentos.php" class="mt-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                                        Crear Primer Documento
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movimientos as $mov): ?>
                            <?php
                            // Obtener información del trabajador que recibe
                            $trabajador = $trabajadorRepo->obtenerPorMatricula($mov->getMatriculaRecibe());
                            $nombreTrabajador = $trabajador ? $trabajador->getNombre() : 'N/A';
                            
                            // Obtener cantidad de bienes
                            $detalles = $detalleRepo->buscarPorMovimiento($mov->getIdMovimiento());
                            $cantidadBienes = count($detalles);
                            ?>
                            <tr class="documento-row hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group" 
                                data-id="<?php echo $mov->getIdMovimiento(); ?>"
                                data-folio="<?php echo htmlspecialchars($mov->getFolio()); ?>"
                                data-tipo="<?php echo htmlspecialchars($mov->getTipoMovimiento()); ?>"
                                data-responsable="<?php echo htmlspecialchars($nombreTrabajador); ?>"
                                data-fecha="<?php echo htmlspecialchars($mov->getFecha()); ?>"
                                data-area="<?php echo htmlspecialchars($mov->getArea()); ?>">
                                
                                <td class="p-4 text-sm font-medium text-imss-dark dark:text-white">
                                    <?php echo htmlspecialchars($mov->getFolio()); ?>
                                </td>
                                
                                <td class="p-4 text-sm">
                                    <?php 
                                    $tipo = $mov->getTipoMovimiento();
                                    $colorClasses = [
                                        'Resguardo' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
                                        'Prestamo' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                                        'Constancia de salida' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200'
                                    ];
                                    $colorClass = isset($colorClasses[$tipo]) ? $colorClasses[$tipo] : 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $colorClass; ?>">
                                        <?php echo htmlspecialchars($tipo); ?>
                                    </span>
                                </td>
                                
                                <td class="p-4 text-sm text-imss-gray dark:text-gray-400 hidden md:table-cell">
                                    <?php echo htmlspecialchars($nombreTrabajador); ?>
                                </td>
                                
                                <td class="p-4 text-sm text-imss-gray dark:text-gray-400 hidden lg:table-cell">
                                    <?php echo date('d/m/Y', strtotime($mov->getFecha())); ?>
                                </td>
                                
                                <td class="p-4 text-sm text-imss-gray dark:text-gray-400 hidden xl:table-cell">
                                    <?php echo $mov->getArea() ? htmlspecialchars($mov->getArea()) : '—'; ?>
                                </td>
                                
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button 
                                            onclick="verDetalleDocumento(<?php echo $mov->getIdMovimiento(); ?>)"
                                            class="p-1.5 text-imss-gray hover:text-primary hover:bg-primary/10 rounded transition-colors" 
                                            title="Ver detalles">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </button>
                                        <button 
                                            onclick="descargarDocumento(<?php echo $mov->getIdMovimiento(); ?>)"
                                            class="p-1.5 text-imss-gray hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded transition-colors" 
                                            title="Descargar PDF">
                                            <span class="material-symbols-outlined text-[20px]">download</span>
                                        </button>
                                        <button 
                                            onclick="eliminarDocumento(<?php echo $mov->getIdMovimiento(); ?>)"
                                            class="p-1.5 text-imss-gray hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-colors" 
                                            title="Eliminar">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-white dark:bg-[#1e2a1e] border-t border-imss-border dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div id="pagination-info" class="text-sm text-imss-gray dark:text-gray-400"></div>
            <div id="pagination-controls" class="flex items-center gap-2"></div>
        </div>
    </div>
</main>

<footer class="bg-white dark:bg-[#1e2a1e] border-t border-imss-border dark:border-gray-800 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-imss-gray dark:text-gray-400">
                © <?php echo date('Y'); ?> IMSS - Sistema de Control de Bienes
            </p>
        </div>
    </div>
</footer>

<!-- Modal Detalle Documento -->
<div id="modal-detalle-documento" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]">
    <div class="modal-overlay absolute w-full h-full bg-black opacity-50" onclick="toggleModal('modal-detalle-documento')"></div>
    <div class="bg-white dark:bg-[#1e2a1e] w-11/12 md:max-w-4xl mx-auto rounded-xl shadow-2xl z-50 overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-imss-border dark:border-gray-800 flex justify-between items-center bg-gradient-to-r from-primary/5 to-transparent sticky top-0 bg-white dark:bg-[#1e2a1e] z-10">
            <h3 class="text-xl font-black text-primary flex items-center gap-2">
                <span class="material-symbols-outlined">description</span> 
                DETALLE DEL DOCUMENTO
            </h3>
            <button onclick="toggleModal('modal-detalle-documento')" class="text-gray-400 hover:text-red-500 transition">
                <span class="material-symbols-outlined text-3xl">close</span>
            </button>
        </div>
        <div id="detalle-documento-content" class="p-8"></div>
    </div>
</div>

<script src="assets/js/gestion_documentos.js?v=<?php echo time(); ?>"></script>
<?php require __DIR__ . '/layouts/scripts.php'; ?>

</body>
</html>