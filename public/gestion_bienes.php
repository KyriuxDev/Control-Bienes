<?php
// public/gestion_bienes.php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLBienRepository;

$db = Database::getInstance();
$pdo = $db->getConnection();

$bienRepo = new MySQLBienRepository($pdo);

// Obtener todos los bienes
$bienes = $bienRepo->obtenerTodos();

// Estadísticas básicas
$totalBienes = count($bienes);
// Inicializamos las variables en 0
$bienesBC = 0;
$bienesBMNC = 0;
$bienesBMC = 0;
$bienesBPS = 0;

foreach ($bienes as $b) {
    $naturaleza = $b->getNaturaleza();
    
    if ($naturaleza === 'BC') {
        $bienesBC++;
    } elseif ($naturaleza === 'BMNC') {
        $bienesBMNC++;
    } elseif ($naturaleza === 'BMC') {
        $bienesBMC++;
    } elseif ($naturaleza === 'BPS') {
        $bienesBPS++;
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
        <span class="font-medium text-primary">Gestión de Bienes</span>
    </div>

    <!-- Header & Actions -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 pb-2 border-b border-imss-border dark:border-gray-800">
        <div class="space-y-1">
            <h2 class="text-3xl font-bold text-imss-dark dark:text-white tracking-tight">Catálogo de Bienes</h2>
            <p class="text-imss-gray dark:text-gray-400 text-base">Gestione, busque y edite el inventario de activos institucionales.</p>
        </div>
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <button onclick="exportarBienes()" class="flex-1 lg:flex-none h-10 px-4 bg-white dark:bg-surface-dark border border-imss-border dark:border-gray-800 text-imss-dark dark:text-white text-sm font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-center gap-2 transition-all">
                <span class="material-symbols-outlined">download</span>
                Exportar
            </button>
            <button onclick="abrirModalNuevoBien()" class="flex-1 lg:flex-none h-10 px-5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-dark flex items-center justify-center gap-2 shadow-sm transition-all hover:shadow-md">
                <span class="material-symbols-outlined">add</span>
                Nuevo Bien
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-[#1e2a1e] p-4 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary">
                    <span class="material-symbols-outlined">inventory_2</span>
                </div>
                <div>
                    <p class="text-xs text-imss-gray dark:text-gray-400 font-medium">Total</p>
                    <p class="text-2xl font-bold text-imss-dark dark:text-white"><?php echo $totalBienes; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e2a1e] p-4 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600">
                    <span class="material-symbols-outlined">shopping_cart</span>
                </div>
                <div>
                    <p class="text-xs text-imss-gray dark:text-gray-400 font-medium">BC</p>
                    <p class="text-2xl font-bold text-imss-dark dark:text-white"><?php echo $bienesBC; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e2a1e] p-4 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600">
                    <span class="material-symbols-outlined">chair</span>
                </div>
                <div>
                    <p class="text-xs text-imss-gray dark:text-gray-400 font-medium">BMNC</p>
                    <p class="text-2xl font-bold text-imss-dark dark:text-white"><?php echo $bienesBMNC; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e2a1e] p-4 rounded-xl border border-imss-border dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-600">
                    <span class="material-symbols-outlined">medical_services</span>
                </div>
                <div>
                    <p class="text-xs text-imss-gray dark:text-gray-400 font-medium">BMC</p>
                    <p class="text-2xl font-bold text-imss-dark dark:text-white"><?php echo $bienesBMC; ?></p>
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
                placeholder="Buscar por descripción, marca, modelo o serie..."
                type="text"
                onkeyup="filtrarBienes()"/>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative group">
                <label class="block text-xs font-semibold text-imss-gray dark:text-gray-400 mb-1.5 uppercase tracking-wide">Naturaleza</label>
                <div class="relative">
                    <select id="filter-naturaleza" onchange="filtrarBienes()" class="appearance-none w-full bg-white dark:bg-[#1e2a1e] border border-imss-border dark:border-gray-800 text-imss-dark dark:text-white text-sm rounded-lg p-2.5 pr-8 focus:ring-1 focus:ring-primary outline-none cursor-pointer">
                        <option value="">Todas las naturalezas</option>
                        <option value="BC">BC - Bienes de Consumo</option>
                        <option value="BMNC">BMNC - Bienes Muebles No Capitalizables</option>
                        <option value="BMC">BMC - Bienes Muebles Capitalizables</option>
                        <option value="BPS">BPS - Bienes de Programas Sociales</option>
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
            Mostrando <span id="visible-count"><?php echo $totalBienes; ?></span> de <span id="total-count"><?php echo $totalBienes; ?></span> bienes
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 overflow-hidden flex flex-col flex-1">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="bg-background-light dark:bg-background-dark border-b border-imss-border dark:border-gray-800">
                    <tr>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider w-[100px]">ID</th>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider">Descripción</th>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Naturaleza</th>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Marca/Modelo</th>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider hidden xl:table-cell">Serie</th>
                        <th class="p-4 text-xs font-bold text-imss-gray dark:text-gray-400 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y divide-imss-border dark:divide-gray-800">
                    <?php if (empty($bienes)): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center">
                                <div class="flex flex-col items-center gap-3 text-imss-gray dark:text-gray-400">
                                    <span class="material-symbols-outlined text-5xl">inbox</span>
                                    <p class="font-medium">No hay bienes registrados</p>
                                    <button onclick="abrirModalNuevoBien()" class="mt-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                                        Agregar Primer Bien
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bienes as $bien): ?>
                            <tr class="bien-row hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group" 
                                data-id="<?php echo $bien->getIdBien(); ?>"
                                data-naturaleza="<?php echo htmlspecialchars($bien->getNaturaleza()); ?>"
                                data-descripcion="<?php echo htmlspecialchars($bien->getDescripcion()); ?>"
                                data-marca="<?php echo htmlspecialchars($bien->getMarca()); ?>"
                                data-modelo="<?php echo htmlspecialchars($bien->getModelo()); ?>"
                                data-serie="<?php echo htmlspecialchars($bien->getSerie()); ?>">
                                
                                <td class="p-4 text-sm font-medium text-imss-dark dark:text-white">
                                    #<?php echo str_pad($bien->getIdBien(), 4, '0', STR_PAD_LEFT); ?>
                                </td>
                                
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary flex-shrink-0">
                                            <span class="material-symbols-outlined">inventory_2</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-imss-dark dark:text-white">
                                                <?php echo htmlspecialchars($bien->getDescripcion()); ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="p-4 text-sm text-imss-gray dark:text-gray-400 hidden md:table-cell">
                                    <?php 
                                    $naturaleza = $bien->getNaturaleza();
                                    $colorClasses = [
                                        'BC' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                                        'BMNC' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
                                        'BMC' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200',
                                        'BPS' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200'
                                    ];
                                    $colorClass = isset($colorClasses[$naturaleza]) ? $colorClasses[$naturaleza] : 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $colorClass; ?>">
                                        <?php echo htmlspecialchars($naturaleza); ?>
                                    </span>
                                </td>
                                
                                <td class="p-4 text-sm text-imss-gray dark:text-gray-400 hidden lg:table-cell">
                                    <?php 
                                    $marca = $bien->getMarca();
                                    $modelo = $bien->getModelo();
                                    if ($marca || $modelo) {
                                        echo htmlspecialchars($marca . ($marca && $modelo ? ' - ' : '') . $modelo);
                                    } else {
                                        echo '<span class="text-gray-400">—</span>';
                                    }
                                    ?>
                                </td>
                                
                                <td class="p-4 text-sm text-imss-gray dark:text-gray-400 hidden xl:table-cell">
                                    <?php 
                                    $serie = $bien->getSerie();
                                    echo $serie ? htmlspecialchars($serie) : '<span class="text-gray-400">—</span>';
                                    ?>
                                </td>
                                
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button 
                                            onclick="verDetalleBien(<?php echo $bien->getIdBien(); ?>)"
                                            class="p-1.5 text-imss-gray hover:text-primary hover:bg-primary/10 rounded transition-colors" 
                                            title="Ver detalles">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </button>
                                        <button 
                                            onclick="editarBien(<?php echo $bien->getIdBien(); ?>)"
                                            class="p-1.5 text-imss-gray hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded transition-colors" 
                                            title="Editar">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </button>
                                        <button 
                                            onclick="eliminarBien(<?php echo $bien->getIdBien(); ?>)"
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

<!-- Modal Bien (Modificado) -->
<div id="modal-bien" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]">
    <div class="modal-overlay absolute w-full h-full bg-black opacity-50" onclick="cerrarModalBien()"></div>
    <div class="bg-white dark:bg-[#1e2a1e] w-11/12 md:max-w-3xl mx-auto rounded-xl shadow-2xl z-50 overflow-y-auto max-h-[90vh]">
        <div class="p-6 border-b border-imss-border dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-primary/5 to-transparent">
            <h3 id="modal-bien-title" class="text-xl font-black text-primary flex items-center gap-2">
                <span class="material-symbols-outlined">inventory_2</span> 
                NUEVO REGISTRO DE BIEN
            </h3>
            <button onclick="cerrarModalBien()" class="text-gray-400 hover:text-red-500 transition">
                <span class="material-symbols-outlined text-3xl">close</span>
            </button>
        </div>
        <form id="form-bien" class="p-8 space-y-6">
            <input type="hidden" name="id_bien" id="id_bien" value="">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Descripción del Bien *</label>
                    <input type="text" name="descripcion" id="descripcion" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. Monitor HP 24 pulgadas LED" required>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Naturaleza *</label>
                    <select name="naturaleza" id="naturaleza" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" required>
                        <option value="BC">Bienes de Consumo (BC)</option>
                        <option value="BMNC" selected>Bienes Muebles No Capitalizables (BMNC)</option>
                        <option value="BMC">Bienes Muebles Capitalizables (BMC)</option>
                        <option value="BPS">Bienes de Programas Sociales (BPS)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Marca</label>
                    <input type="text" name="marca" id="marca" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. HP">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Modelo</label>
                    <input type="text" name="modelo" id="modelo" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. EliteDisplay">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Serie</label>
                    <input type="text" name="serie" id="serie" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. SN123456">
                </div>
            </div>
            <div class="pt-4 border-t border-imss-border dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="cerrarModalBien()" class="px-6 py-2 rounded-lg text-gray-500 font-bold hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancelar
                </button>
                <button type="submit" id="btn-submit-bien" class="px-6 py-2 rounded-lg bg-primary text-white font-bold hover:bg-green-800 transition shadow-md">
                    Crear Registro
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detalle Bien -->
<div id="modal-detalle-bien" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]">
    <div class="modal-overlay absolute w-full h-full bg-black opacity-50" onclick="toggleModal('modal-detalle-bien')"></div>
    <div class="bg-white dark:bg-[#1e2a1e] w-11/12 md:max-w-2xl mx-auto rounded-xl shadow-2xl z-50 overflow-hidden">
        <div class="p-6 border-b border-imss-border dark:border-gray-800 flex justify-between items-center bg-gradient-to-r from-primary/5 to-transparent">
            <h3 class="text-xl font-black text-primary flex items-center gap-2">
                <span class="material-symbols-outlined">info</span> 
                DETALLE DEL BIEN
            </h3>
            <button onclick="toggleModal('modal-detalle-bien')" class="text-gray-400 hover:text-red-500 transition">
                <span class="material-symbols-outlined text-3xl">close</span>
            </button>
        </div>
        <div id="detalle-bien-content" class="p-8"></div>
    </div>
</div>

<script src="assets/js/gestion_bienes.js?v=<?php echo time(); ?>"></script>
<?php require __DIR__ . '/layouts/scripts.php'; ?>

</body>
</html>