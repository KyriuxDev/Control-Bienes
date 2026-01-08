<?php
// public/generador_documentos.php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;

$db = Database::getInstance();
$pdo = $db->getConnection();

$trabajadorRepo = new MySQLTrabajadorRepository($pdo);
$bienRepo = new MySQLBienRepository($pdo);

$trabajadores = $trabajadorRepo->obtenerTodos();
$bienesCatalogo = $bienRepo->obtenerTodos();
require __DIR__ . '/layouts/head.php';
?>

<body class="bg-background-light dark:bg-background-dark text-imss-dark dark:text-white font-display antialiased overflow-x-hidden flex flex-col min-h-screen">

<?php require __DIR__ . '/layouts/topnav.php'; ?>

<!-- Main Content Wrapper -->
<main class="flex-grow w-full max-w-[1200px] mx-auto px-4 sm:px-6 py-6 pb-24">
    
    <?php require __DIR__ . '/partials/breadcrumbs.php'; ?>
    <?php require __DIR__ . '/partials/sticky_tabs.php'; ?>

    <!-- Page Heading -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-bold text-imss-dark dark:text-white tracking-tight">Generación de Documentos</h2>
            <p class="mt-2 text-imss-gray dark:text-gray-400 max-w-2xl">Complete el formulario para emitir constancias de salida, resguardos o préstamos de bienes institucionales.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-primary bg-white border border-imss-border rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:bg-gray-800 dark:border-gray-700 dark:text-green-400 dark:hover:bg-gray-700">
                <span class="material-symbols-outlined text-lg mr-2">help</span>
                Guía de Usuario
            </button>
        </div>
    </div>

    <!-- Form Content -->
    <form id="document-form" action="procesar_pdf.php" method="POST" class="space-y-8 mt-2">
        
        <!-- SECTION 1: DATOS GENERALES -->
        <section id="general" class="scroll-mt-32">
            <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 bg-gray-50/50 dark:bg-white/5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">description</span>
                        Información del Documento
                    </h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    <!-- Tipo de Documento con Checkboxes -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-3">
                            Tipo(s) de Documento <span class="text-red-500">*</span>
                            <span class="text-xs font-normal text-imss-gray ml-2">(Puede seleccionar uno o más)</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Resguardo -->
                            <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-primary/5 transition-all border-imss-border dark:border-gray-700 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="checkbox" name="tipos_movimiento[]" value="Resguardo" class="peer sr-only" onchange="updateConstanciaFields()">
                                <div class="flex items-center gap-3 w-full">
                                    <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary peer-checked:bg-primary peer-checked:text-white transition-colors">
                                        <span class="material-symbols-outlined">shield_person</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-sm text-imss-dark dark:text-white">Resguardo</p>
                                        <p class="text-xs text-imss-gray dark:text-gray-400">Formato CMB-3</p>
                                    </div>
                                </div>
                                <span class="absolute top-2 right-2 size-5 rounded-full border-2 border-gray-300 peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-sm hidden peer-checked:block">check</span>
                                </span>
                            </label>

                            <!-- Préstamo -->
                            <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/10 transition-all border-imss-border dark:border-gray-700 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-900/20">
                                <input type="checkbox" name="tipos_movimiento[]" value="Prestamo" class="peer sr-only" onchange="updateConstanciaFields()">
                                <div class="flex items-center gap-3 w-full">
                                    <div class="flex items-center justify-center size-10 rounded-lg bg-blue-100 text-blue-600 peer-checked:bg-blue-500 peer-checked:text-white transition-colors dark:bg-blue-900/30">
                                        <span class="material-symbols-outlined">swap_horiz</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-sm text-imss-dark dark:text-white">Préstamo</p>
                                        <p class="text-xs text-imss-gray dark:text-gray-400">Temporal</p>
                                    </div>
                                </div>
                                <span class="absolute top-2 right-2 size-5 rounded-full border-2 border-gray-300 peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-sm hidden peer-checked:block">check</span>
                                </span>
                            </label>

                            <!-- Constancia de Salida -->
                            <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-orange-50 dark:hover:bg-orange-900/10 transition-all border-imss-border dark:border-gray-700 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 dark:has-[:checked]:bg-orange-900/20">
                                <input type="checkbox" name="tipos_movimiento[]" value="Constancia de salida" class="peer sr-only" onchange="updateConstanciaFields()">
                                <div class="flex items-center gap-3 w-full">
                                    <div class="flex items-center justify-center size-10 rounded-lg bg-orange-100 text-orange-600 peer-checked:bg-orange-500 peer-checked:text-white transition-colors dark:bg-orange-900/30">
                                        <span class="material-symbols-outlined">logout</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-sm text-imss-dark dark:text-white">Constancia Salida</p>
                                        <p class="text-xs text-imss-gray dark:text-gray-400">Salida de bien</p>
                                    </div>
                                </div>
                                <span class="absolute top-2 right-2 size-5 rounded-full border-2 border-gray-300 peer-checked:border-orange-500 peer-checked:bg-orange-500 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-sm hidden peer-checked:block">check</span>
                                </span>
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-imss-gray dark:text-gray-400 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">info</span>
                            Seleccione uno o más formatos para generar simultáneamente
                        </p>
                    </div>

                    <!-- Campo fecha de devolución para préstamo (oculto por defecto) -->
                    <div class="col-span-1 md:col-span-2 hidden" id="fecha-devolucion-container">
                        <label for="fecha_devolucion_prestamo" class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">
                            Fecha de Devolución del Préstamo <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-imss-gray text-lg">event</span>
                            </div>
                            <input type="date" 
                                   id="fecha_devolucion_prestamo" 
                                   name="fecha_devolucion_prestamo" 
                                   class="block w-full pl-10 pr-3 py-2.5 border-imss-border focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm">
                        </div>
                        <p class="mt-1 text-xs text-imss-gray dark:text-gray-500">
                            Se calculará automáticamente el número de días de préstamo.
                            <span id="dias-calculados" class="font-bold text-blue-600 dark:text-blue-400"></span>
                        </p>
                        <!-- Campo oculto para enviar los días calculados -->
                        <input type="hidden" id="dias_prestamo" name="dias_prestamo" value="">
                    </div>

                    <!-- Fecha de Emisión -->
                    <div class="col-span-1">
                        <label for="fecha" class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">
                            Fecha de Emisión <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-imss-gray text-lg">calendar_today</span>
                            </div>
                            <input type="date" 
                                   id="fecha" 
                                   name="fecha" 
                                   value="<?php echo date('Y-m-d'); ?>"
                                   required
                                   class="block w-full pl-10 pr-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm">
                        </div>
                    </div>

                    <!-- Lugar -->
                    <div class="col-span-1">
                        <label for="lugar" class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">
                            Lugar
                        </label>
                        <input type="text" 
                               id="lugar" 
                               name="lugar" 
                               value="Oaxaca de Juárez, Oaxaca"
                               class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm">
                    </div>

                    <!-- Nota informativa sobre folio automático -->
                    <div class="col-span-1 md:col-span-2">
                        <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg dark:bg-green-900/20">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-green-500">check_circle</span>
                                <p class="text-sm text-green-800 dark:text-green-200">
                                    <span class="font-bold">Folio Automático:</span> El sistema generará automáticamente el folio del documento al momento de guardarlo (formato: AÑO/NÚMERO).
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Área (Campo oculto con valor fijo) -->
                    <input type="hidden" 
                           id="area" 
                           name="area" 
                           value="Coordinación de Informática">
                </div>
            </div>
        </section>

        <!-- SECTION 2: RESPONSABLES -->
        <section id="responsables" class="scroll-mt-32">
            <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 bg-gray-50/50 dark:bg-white/5 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-col">
                        <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">group</span>
                            Responsables y Firmas
                        </h3>
                        <p class="text-xs text-imss-gray dark:text-gray-400 mt-0.5">Defina quién recibe y quién entrega los bienes.</p>
                    </div>
                    <button type="button" 
                            onclick="toggleModal('modal-trabajador')"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent dark:bg-accent dark:hover:bg-accent/80">
                        <span class="material-symbols-outlined text-lg mr-2">person_add</span>
                        Nuevo Trabajador
                    </button>
                </div>
                
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Columna Quien RECIBE -->
                    <div class="rounded-lg border-2 border-imss-border p-5 bg-blue-50/30 dark:border-gray-700 dark:bg-blue-900/10">
                        <h4 class="text-sm uppercase tracking-wide text-imss-gray font-bold mb-4 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-blue-500"></span> Quien Recibe
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">
                                    Seleccionar Trabajador <span class="text-red-500">*</span>
                                </label>
                                <select name="matricula_recibe" 
                                        id="matricula_recibe" 
                                        class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        required 
                                        onchange="mostrarDatosTrabajador(this, 'recibe')">
                                    <option value="">-- Seleccione un trabajador --</option>
                                    <?php foreach($trabajadores as $t): ?>
                                        <option value="<?php echo $t->matricula; ?>" 
                                            data-mat="<?php echo htmlspecialchars($t->matricula); ?>"
                                            data-nombre="<?php echo htmlspecialchars($t->nombre); ?>"
                                            data-cargo="<?php echo htmlspecialchars($t->cargo); ?>"
                                            data-ads="<?php echo htmlspecialchars($t->adscripcion); ?>"
                                            data-tel="<?php echo htmlspecialchars($t->telefono); ?>">
                                            <?php echo htmlspecialchars($t->nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div id="panel-datos-recibe" class="p-4 bg-white border border-dashed border-primary/30 rounded-lg dark:bg-gray-800 dark:border-gray-600 hidden">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Matrícula</p>
                                        <p id="val-mat-recibe" class="font-bold text-sm"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Teléfono</p>
                                        <p id="val-tel-recibe" class="text-sm"></p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Cargo</p>
                                        <p id="val-cargo-recibe" class="text-sm"></p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Adscripción</p>
                                        <p id="val-ads-recibe" class="text-sm"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Quien ENTREGA -->
                    <div class="rounded-lg border-2 border-imss-border p-5 bg-green-50/30 dark:border-gray-700 dark:bg-green-900/10">
                        <h4 class="text-sm uppercase tracking-wide text-imss-gray font-bold mb-4 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-green-500"></span> Quien Entrega
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">
                                    Seleccionar Trabajador <span class="text-red-500">*</span>
                                </label>
                                <select name="matricula_entrega" 
                                        id="matricula_entrega" 
                                        class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        required 
                                        onchange="mostrarDatosTrabajador(this, 'entrega')">
                                    <option value="">-- Seleccione un trabajador --</option>
                                    <?php foreach($trabajadores as $t): ?>
                                        <option value="<?php echo $t->matricula; ?>" 
                                            data-mat="<?php echo htmlspecialchars($t->matricula); ?>"
                                            data-nombre="<?php echo htmlspecialchars($t->nombre); ?>"
                                            data-cargo="<?php echo htmlspecialchars($t->cargo); ?>"
                                            data-ads="<?php echo htmlspecialchars($t->adscripcion); ?>"
                                            data-tel="<?php echo htmlspecialchars($t->telefono); ?>">
                                            <?php echo htmlspecialchars($t->nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div id="panel-datos-entrega" class="p-4 bg-white border border-dashed border-primary/30 rounded-lg dark:bg-gray-800 dark:border-gray-600 hidden">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Matrícula</p>
                                        <p id="val-mat-entrega" class="font-bold text-sm"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Teléfono</p>
                                        <p id="val-tel-entrega" class="text-sm"></p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Cargo</p>
                                        <p id="val-cargo-entrega" class="text-sm"></p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Adscripción</p>
                                        <p id="val-ads-entrega" class="text-sm"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 3: BIENES -->
        <section id="bienes" class="scroll-mt-32">
            <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 bg-gray-50/50 dark:bg-white/5 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-col">
                        <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">inventory_2</span>
                            Bienes Involucrados
                        </h3>
                        <p class="text-xs text-imss-gray dark:text-gray-400 mt-0.5">Agregue los bienes que formarán parte de este documento.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" 
                                onclick="toggleModal('modal-bien')"
                                class="inline-flex items-center px-3 py-2 border border-imss-border text-sm font-medium rounded-lg text-imss-dark bg-white hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700">
                            <span class="material-symbols-outlined text-lg mr-2">add_box</span>
                            Nuevo Bien
                        </button>
                        <button type="button" 
                                onclick="agregarFilaBien()"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <span class="material-symbols-outlined text-lg mr-2">add</span>
                            Agregar Bien
                        </button>
                    </div>
                </div>

                <!-- Opciones Globales de Bienes -->
                <div class="px-6 pt-6 pb-4 bg-gray-50/50 dark:bg-white/5 border-b border-imss-border dark:border-gray-700">
                    <h4 class="text-sm font-bold text-imss-dark dark:text-white mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-base">settings</span>
                        Configuración General de Bienes
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Estado físico global -->
                        <div>
                            <label class="block text-xs font-medium text-imss-dark dark:text-gray-200 mb-1">
                                Estado Físico (para todos los bienes)
                            </label>
                            <select name="estado_general" id="estado_general" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
                                <option value="Nuevo">Nuevo</option>
                                <option value="Buenas condiciones" selected>Buenas condiciones</option>
                                <option value="Dañado">Dañado</option>
                                <option value="Otro">Otro (especificar)</option>
                            </select>
                        </div>
                        
                        <!-- Input para "Otro" estado (oculto por defecto) -->
                        <div id="otro-estado-container" class="hidden">
                            <label class="block text-xs font-medium text-imss-dark dark:text-gray-200 mb-1">
                                Especifique el estado
                            </label>
                            <input type="text" 
                                   name="estado_otro" 
                                   id="estado_otro"
                                   placeholder="Ej. Desgastado, En reparación..."
                                   class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
                        </div>
                        
                        <!-- Sujeto a devolución (solo visible para Constancia de Salida) -->
                        <div class="constancia-only hidden">
                            <label class="block text-xs font-medium text-imss-dark dark:text-gray-200 mb-1">
                                ¿Sujeto a devolución?
                            </label>
                            <div class="flex items-center gap-4 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" 
                                           name="sujeto_devolucion_global" 
                                           value="1" 
                                           class="text-primary focus:ring-primary">
                                    <span class="text-sm text-imss-dark dark:text-white">Sí</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" 
                                           name="sujeto_devolucion_global" 
                                           value="0" 
                                           checked
                                           class="text-primary focus:ring-primary">
                                    <span class="text-sm text-imss-dark dark:text-white">No</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Bienes -->
                <div class="p-6">
                   <div id="contenedor-bienes" class="space-y-3">
                        <div class="bien-row flex gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 items-start hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary flex-shrink-0 mt-1">
                                <span class="material-symbols-outlined">inventory</span>
                            </div>
                            <div class="flex-grow space-y-3">
                                <select name="bienes[0][id_bien]" class="bien-select w-full rounded-lg border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                    <option value="">-- Seleccionar Bien --</option>
                                    <?php foreach($bienesCatalogo as $b): ?>
                                        <option value="<?php echo $b->id_bien; ?>">
                                            <?php echo htmlspecialchars($b->serie ?: 'BIEN-'.$b->id_bien); ?> - <?php echo htmlspecialchars($b->descripcion); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-bold text-gray-500 whitespace-nowrap">Cantidad:</label>
                                    <input type="number" 
                                        name="bienes[0][cantidad]" 
                                        value="1" 
                                        min="1" 
                                        class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                </div>
                            </div>
                            <button type="button" onclick="eliminarFilaBien(this)" class="text-red-500 hover:bg-red-50 p-2 rounded-lg mt-1">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </div>

                    </div>
                    
                    <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg dark:bg-blue-900/20">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-500">info</span>
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <span class="font-bold">Nota:</span> El estado físico y condición de devolución se aplicarán a todos los bienes de este documento.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
</main>

<?php require __DIR__ . '/partials/footer_actions.php'; ?>
<?php require __DIR__ . '/partials/modal_bien.php'; ?>
<?php require __DIR__ . '/partials/modal_trabajador.php'; ?>

<script>
    window.APP_DATA = {
        bienesCatalogo: <?php echo json_encode($bienesCatalogo); ?>
    };
</script>
<?php require __DIR__ . '/layouts/scripts.php'; ?>

</body>
</html>