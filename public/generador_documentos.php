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
?>
<!DOCTYPE html>
<html lang="es" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMSS - Generador de Documentos</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#247528",
                        "primary-dark": "#1b5e1e",
                        "background-light": "#f6f8f6",
                        "background-dark": "#141e14",
                        "imss-gray": "#68826a",
                        "imss-dark": "#121712",
                        "imss-border": "#dde4dd",
                        "accent": "#0df29a"
                    },
                    fontFamily: {
                        "display": ["Inter", "Noto Sans", "sans-serif"],
                        "body": ["Inter", "Noto Sans", "sans-serif"]
                    }
                }
            }
        }
    </script>
    
    <style>
        .modal {
            transition: opacity 0.25s ease;
        }
        body.modal-active {
            overflow-x: hidden;
            overflow-y: visible !important;
        }
        .custom-scrollbar::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.filled {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-imss-dark dark:text-white font-display antialiased overflow-x-hidden flex flex-col min-h-screen">

<!-- TopNavBar -->
<header class="sticky top-0 z-50 bg-white dark:bg-[#1e2a1e] border-b border-imss-border dark:border-[#2a382a] shadow-sm">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo & Title -->
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center size-10 rounded-md bg-primary/10 text-primary">
                    <span class="material-symbols-outlined text-3xl">health_and_safety</span>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-lg font-bold leading-tight tracking-tight text-imss-dark dark:text-white">IMSS Control de Bienes</h1>
                    <span class="text-xs font-medium text-imss-gray dark:text-gray-400">Sistema Administrativo</span>
                </div>
            </div>
            
            <!-- Navigation 
            <nav class="hidden md:flex items-center gap-8">
                <a href="#" class="text-sm font-medium text-imss-dark hover:text-primary transition-colors dark:text-gray-200 dark:hover:text-primary">Inicio</a>
                <a href="#" class="text-sm font-medium text-primary border-b-2 border-primary pb-0.5 dark:text-primary">Gestión</a>
                <a href="#" class="text-sm font-medium text-imss-dark hover:text-primary transition-colors dark:text-gray-200 dark:hover:text-primary">Reportes</a>
            </nav>
            -->
            
            <!-- Actions & Profile-->
            <!-- 
            <div class="flex items-center gap-4">
                <button class="flex items-center justify-center size-10 rounded-full hover:bg-imss-border/30 text-imss-dark dark:text-gray-200 transition-colors relative">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="absolute top-2 right-2 size-2 bg-red-500 rounded-full ring-2 ring-white dark:ring-[#1e2a1e]"></span>
                </button> -->
                <div class="h-8 w-px bg-imss-border dark:bg-gray-700 mx-1"></div>
                <div class="flex items-center gap-3">
                    <div class="hidden lg:flex flex-col items-end">
                        <span class="text-sm font-bold text-imss-dark dark:text-white">Administrador</span>
                        <span class="text-xs text-imss-gray dark:text-gray-400">Sistema</span>
                    </div>
                    <div class="size-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-sm ring-2 ring-white dark:ring-gray-700 shadow-sm">
                        AD
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Main Content Wrapper -->
<main class="flex-grow w-full max-w-[1200px] mx-auto px-4 sm:px-6 py-6 pb-24">
    <!-- Breadcrumbs -->
    <nav aria-label="Breadcrumb" class="flex mb-6">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="#" class="inline-flex items-center text-sm font-medium text-imss-gray hover:text-primary dark:text-gray-400">
                    <span class="material-symbols-outlined text-[18px] mr-1">home</span>
                    Inicio
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="material-symbols-outlined text-imss-gray text-lg mx-1">chevron_right</span>
                    <a href="#" class="text-sm font-medium text-imss-gray hover:text-primary dark:text-gray-400">Gestión de Bienes</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <span class="material-symbols-outlined text-imss-gray text-lg mx-1">chevron_right</span>
                    <span class="text-sm font-medium text-primary">Nuevo Documento</span>
                </div>
            </li>
        </ol>
    </nav>

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

    <!-- Sticky Form Navigation (Tabs) -->
    <div class="sticky top-[65px] z-40 bg-background-light dark:bg-background-dark pt-2 pb-4 -mx-4 px-4 sm:mx-0 sm:px-0">
        <div class="flex overflow-x-auto gap-8 border-b border-imss-border dark:border-gray-700 no-scrollbar">
            <a href="#general" class="whitespace-nowrap pb-3 border-b-2 border-primary text-primary font-bold text-sm flex items-center gap-2">
                <span class="flex items-center justify-center size-6 rounded-full bg-primary text-white text-xs">1</span>
                Datos Generales
            </a>
            <a href="#responsables" class="whitespace-nowrap pb-3 border-b-2 border-transparent text-imss-gray hover:text-imss-dark hover:border-gray-300 font-medium text-sm flex items-center gap-2 dark:text-gray-400 dark:hover:text-white transition-colors">
                <span class="flex items-center justify-center size-6 rounded-full bg-imss-border text-imss-gray text-xs dark:bg-gray-700 dark:text-gray-400">2</span>
                Responsables
            </a>
            <a href="#bienes" class="whitespace-nowrap pb-3 border-b-2 border-transparent text-imss-gray hover:text-imss-dark hover:border-gray-300 font-medium text-sm flex items-center gap-2 dark:text-gray-400 dark:hover:text-white transition-colors">
                <span class="flex items-center justify-center size-6 rounded-full bg-imss-border text-imss-gray text-xs dark:bg-gray-700 dark:text-gray-400">3</span>
                Bienes Involucrados
            </a>
        </div>
    </div>

    <!-- Form Content -->
    <form action="procesar_pdf.php" method="POST" class="space-y-8 mt-2">
        
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
                    <!-- Tipo de Documento -->
                    <div class="col-span-1 md:col-span-2">
                        <label for="doc-type" class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">
                            Tipo de Documento <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-primary/5 transition-all border-imss-border dark:border-gray-700 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="checkbox" name="docs_a_generar[]" value="resguardo" class="sr-only peer" checked>
                                <div class="flex items-center gap-3 w-full">
                                    <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary peer-checked:bg-primary peer-checked:text-white transition-colors">
                                        <span class="material-symbols-outlined">shield_person</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-sm text-imss-dark dark:text-white">Resguardo Individual</p>
                                        <p class="text-xs text-imss-gray dark:text-gray-400">Formato CMB-3</p>
                                    </div>
                                </div>
                                <span class="absolute top-2 right-2 size-5 rounded-full border-2 border-gray-300 peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-sm hidden peer-checked:block">check</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Folio -->
                    <div class="col-span-1">
                        <label for="folio" class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">
                            Folio del Documento
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="folio" 
                                   name="folio_resguardo" 
                                   placeholder="Ej. 2026/054"
                                   class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm">
                        </div>
                        <p class="mt-1 text-xs text-imss-gray dark:text-gray-500">Ingrese el folio del documento oficial.</p>
                    </div>

                    <!-- Fecha de Emisión -->
                    <div class="col-span-1">
                        <label for="date" class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">
                            Lugar y Fecha <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-imss-gray text-lg">calendar_today</span>
                            </div>
                            <?php
                                $meses = array("1"=>"enero", "2"=>"febrero", "3"=>"marzo", "4"=>"abril", "5"=>"mayo", "6"=>"junio", "7"=>"julio", "8"=>"agosto", "9"=>"septiembre", "10"=>"octubre", "11"=>"noviembre", "12"=>"diciembre");
                                $fecha_formateada = "Oaxaca de Juárez, Oaxaca, " . date('j') . " de " . $meses[date('n')] . " de " . date('Y');
                            ?>
                            <input type="text" 
                                   id="date" 
                                   name="lugar_fecha_resguardo" 
                                   value="<?php echo $fecha_formateada; ?>"
                                   readonly
                                   class="block w-full pl-10 pr-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm bg-gray-50 dark:bg-gray-900 cursor-not-allowed">
                        </div>
                    </div>
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
                        <p class="text-xs text-imss-gray dark:text-gray-400 mt-0.5">Defina quién resguarda y quién entrega los bienes.</p>
                    </div>
                    <button type="button" 
                            onclick="toggleModal('modal-trabajador')"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-primary hover:bg-primary-dark 0 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent dark:bg-accent dark:hover:bg-accent/80">
                        <span class="material-symbols-outlined text-lg mr-2">person_add</span>
                        Nuevo Trabajador
                    </button>
                </div>
                
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Columna Resguardante -->
                    <div class="rounded-lg border-2 border-imss-border p-5 bg-blue-50/30 dark:border-gray-700 dark:bg-blue-900/10">
                        <h4 class="text-sm uppercase tracking-wide text-imss-gray font-bold mb-4 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-blue-500"></span> Resguardante
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">
                                    Seleccionar Trabajador <span class="text-red-500">*</span>
                                </label>
                                <select name="trabajador_matricula" 
                                        id="trabajador_matricula" 
                                        class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        required 
                                        onchange="mostrarDatosTrabajador(this)">
                                    <option value="">-- Seleccione un trabajador --</option>
                                    <?php foreach($trabajadores as $t): ?>
                                        <option value="<?php echo $t->matricula; ?>" 
                                            data-mat="<?php echo htmlspecialchars($t->matricula); ?>"
                                            data-cargo="<?php echo htmlspecialchars($t->cargo); ?>"
                                            data-ads="<?php echo htmlspecialchars($t->adscripcion); ?>"
                                            data-tel="<?php echo htmlspecialchars($t->telefono); ?>">
                                            <?php echo htmlspecialchars($t->nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div id="panel-datos" class="p-4 bg-white border border-dashed border-primary/30 rounded-lg dark:bg-gray-800 dark:border-gray-600 hidden">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Matrícula</p>
                                        <p id="val-mat" class="font-bold text-sm"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Teléfono</p>
                                        <p id="val-tel" class="text-sm"></p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Cargo</p>
                                        <p id="val-cargo" class="text-sm"></p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Adscripción</p>
                                        <p id="val-ads" class="text-sm"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Quien Entrega -->
                    <div class="rounded-lg border-2 border-imss-border p-5 bg-green-50/30 dark:border-gray-700 dark:bg-green-900/10">
                        <h4 class="text-sm uppercase tracking-wide text-imss-gray font-bold mb-4 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-green-500"></span> Quien Entrega
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">
                                    Nombre Completo
                                </label>
                                <input type="text" 
                                       name="recibe_resguardo" 
                                       placeholder="Ej. Juan Pérez López"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">
                                    Cargo / Puesto
                                </label>
                                <input type="text" 
                                       name="entrega_resguardo" 
                                       placeholder="Ej. Jefe de Departamento"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
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

                <!-- Lista de Bienes -->
                <div class="p-6">
                    <div id="contenedor-bienes" class="space-y-3">
                        <div class="flex gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 items-center hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary flex-shrink-0">
                                <span class="material-symbols-outlined">inventory</span>
                            </div>
                            <select name="bienes[0][bien_id]" class="flex-grow rounded-lg border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
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
                                       class="w-20 rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg dark:bg-blue-900/20">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-500">info</span>
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <span class="font-bold">Nota:</span> Puede agregar múltiples bienes al documento. Use el botón "Agregar Bien" para añadir más elementos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
</main>

<!-- Sticky Footer Actions -->
<div class="fixed bottom-0 left-0 w-full bg-white dark:bg-[#1e2a1e] border-t border-imss-border dark:border-gray-700 shadow-lg z-50">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 h-20 flex items-center justify-between">
        <button type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-imss-dark bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700">
            Cancelar
        </button>
        <div class="flex items-center gap-3">
            <span class="text-xs text-imss-gray dark:text-gray-400 mr-2 hidden sm:inline-block">
                <span class="material-symbols-outlined text-sm align-middle">schedule</span>
                Autoguardado activo
            </span>
            <button type="button" 
                    onclick="vistaPrevia()"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-primary bg-white border border-primary rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:bg-transparent dark:text-green-400 dark:border-green-400 dark:hover:bg-green-900/30">
                <span class="material-symbols-outlined text-[18px] mr-2">visibility</span>
                Vista Previa
            </button>
            <button type="submit" 
                    form="document-form"
                    onclick="document.querySelector('form').submit()"
                    class="inline-flex items-center px-6 py-2 text-sm font-bold text-white bg-primary rounded-lg shadow-sm hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transform transition hover:scale-[1.02]">
                <span class="material-symbols-outlined text-[20px] mr-2">save_as</span>
                Generar Documento PDF
            </button>
        </div>
    </div>
</div>

<!-- MODAL: NUEVO TRABAJADOR -->
<div id="modal-trabajador" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]">
    <div class="modal-overlay absolute w-full h-full bg-black opacity-50" onclick="toggleModal('modal-trabajador')"></div>
    <div class="bg-white dark:bg-[#1e2a1e] w-11/12 md:max-w-3xl mx-auto rounded-xl shadow-2xl z-50 overflow-y-auto max-h-[90vh]">
        <div class="p-6 border-b border-imss-border dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-primary/5 to-transparent">
            <h3 class="text-xl font-black text-primary flex items-center gap-2">
                <span class="material-symbols-outlined">person_add</span> 
                REGISTRAR TRABAJADOR
            </h3>
            <button onclick="toggleModal('modal-trabajador')" class="text-gray-400 hover:text-red-500 transition">
                <span class="material-symbols-outlined text-3xl">close</span>
            </button>
        </div>
        <form class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Nombre Completo *</label>
                <input type="text" name="nombre" class="w-full rounded-lg border-gray-300 bg-gray-50 focus:ring-accent dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. Juan Pérez López" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Matrícula *</label>
                <input type="text" name="matricula" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="IMSS-0000" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Cargo *</label>
                <input type="text" name="cargo" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. Médico General" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Institucion *</label>
                <input type="text" name="institucion" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. Urgencias">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Adscripción *</label>
                <input type="text" name="adscripcion" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. Urgencias">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Teléfono *</label>
                <input type="tel" name="telefono" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="951 874 7412">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Identificacion *</label>
                <input type="tel" name="identificacion" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="No se JAJAJAJ">
            </div>
            <div class="md:col-span-2 pt-4 border-t border-imss-border dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="toggleModal('modal-trabajador')" class="px-6 py-2 rounded-lg text-gray-500 font-bold hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-accent text-black font-bold hover:brightness-90 transition shadow-md">
                    Guardar Trabajador
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: NUEVO BIEN -->
<div id="modal-bien" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]">
    <div class="modal-overlay absolute w-full h-full bg-black opacity-50" onclick="toggleModal('modal-bien')"></div>
    <div class="bg-white dark:bg-[#1e2a1e] w-11/12 md:max-w-3xl mx-auto rounded-xl shadow-2xl z-50 overflow-y-auto max-h-[90vh]">
        <div class="p-6 border-b border-imss-border dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-primary/5 to-transparent">
            <h3 class="text-xl font-black text-primary flex items-center gap-2">
                <span class="material-symbols-outlined">inventory_2</span> 
                NUEVO REGISTRO DE BIEN
            </h3>
            <button onclick="toggleModal('modal-bien')" class="text-gray-400 hover:text-red-500 transition">
                <span class="material-symbols-outlined text-3xl">close</span>
            </button>
        </div>
        <form class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Descripción del Bien *</label>
                    <input type="text" name="descripcion" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. Monitor HP 24 pulgadas LED" required>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">No. de Inventario / SKU</label>
                    <input type="text" name="identificacion" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="IMSS-2024-001">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Naturaleza</label>
                    <select name="naturaleza" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        <option value="BC">Bienes de Consumo (BC)</option>
                        <option value="BMNC" selected>Bienes Muebles No Capitalizables (BMNC)</option>
                        <option value="BMC">Bienes Muebles Capitalizables (BMC)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Marca</label>
                    <input type="text" name="marca" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. HP">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Modelo</label>
                    <input type="text" name="modelo" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. EliteDisplay">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Serie</label>
                    <input type="text" name="serie" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. SN123456">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Estado</label>
                    <input type="text" name="estado_fisico" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. Excelente">
                </div>
            </div>
            <div class="pt-4 border-t border-imss-border dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="toggleModal('modal-bien')" class="px-6 py-2 rounded-lg text-gray-500 font-bold hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-primary text-white font-bold hover:bg-green-800 transition shadow-md">
                    Crear Registro
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle Modal
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        modal.classList.toggle('opacity-0');
        modal.classList.toggle('pointer-events-none');
        document.body.classList.toggle('modal-active');
    }

    // Mostrar datos del trabajador seleccionado
    function mostrarDatosTrabajador(select) {
        const panel = document.getElementById('panel-datos');
        const opt = select.options[select.selectedIndex];
        if (opt.value) {
            panel.classList.remove('hidden');
            document.getElementById('val-mat').innerText = opt.dataset.mat;
            document.getElementById('val-cargo').innerText = opt.dataset.cargo;
            document.getElementById('val-ads').innerText = opt.dataset.ads;
            document.getElementById('val-tel').innerText = opt.dataset.tel;
        } else {
            panel.classList.add('hidden');
        }
    }

    // Agregar fila de bien
    let bIdx = 1;
    function agregarFilaBien() {
        const div = document.createElement('div');
        div.className = "flex gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 items-center hover:shadow-md transition-shadow animate-pulse";
        div.innerHTML = `
            <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary flex-shrink-0">
                <span class="material-symbols-outlined">inventory</span>
            </div>
            <select name="bienes[${bIdx}][bien_id]" class="flex-grow rounded-lg border-gray-300 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                <option value="">-- Seleccionar Bien --</option>
                <?php foreach($bienesCatalogo as $b): 
                    echo "<option value='".$b->id_bien."'>".
                        htmlspecialchars($b->serie ?: 'BIEN-'.$b->id_bien)." - ".
                        htmlspecialchars($b->descripcion)."</option>"; 
                endforeach; ?>
            </select>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-gray-500 whitespace-nowrap">Cantidad:</label>
                <input type="number" name="bienes[${bIdx}][cantidad]" value="1" min="1" class="w-20 rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-lg transition dark:hover:bg-red-900/20">
                <span class="material-symbols-outlined">delete</span>
            </button>
        `;
        document.getElementById('contenedor-bienes').appendChild(div);
        setTimeout(() => div.classList.remove('animate-pulse'), 300);
        bIdx++;
    }
    // Vista Previa del PDF
    function vistaPrevia() {
        const form = document.querySelector('form');
        
        // Validar que hay un trabajador seleccionado
        const trabajadorId = document.getElementById('trabajador_matricula').value;
        if (!trabajadorId) {
            alert('Por favor seleccione un trabajador');
            return;
        }
        
        // Validar que hay al menos un bien
        const primerBien = document.querySelector('select[name="bienes[0][bien_id]"]').value;
        if (!primerBien) {
            alert('Por favor seleccione al menos un bien');
            return;
        }
        
        // Cambiar temporalmente el action del formulario
        const actionOriginal = form.action;
        form.action = 'vista_previa_pdf.php';
        form.target = '_blank'; // Abrir en nueva pestaña
        
        // Enviar formulario
        form.submit();
        
        // Restaurar configuración original
        setTimeout(() => {
            form.action = actionOriginal;
            form.target = '';
        }, 100);
    }

    // GUARDAR TRABAJADOR
    document.querySelector('#modal-trabajador form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin">refresh</span> Guardando...';
        
        fetch('api/guardar_trabajador.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Agregar al select
                const select = document.getElementById('trabajador_matricula');
                const option = document.createElement('option');
                option.value = data.trabajador.matricula; // Usar matrícula como value
                option.textContent = data.trabajador.nombre;
                option.setAttribute('data-mat', data.trabajador.matricula);
                option.setAttribute('data-cargo', data.trabajador.cargo);
                option.setAttribute('data-ads', data.trabajador.adscripcion);
                option.setAttribute('data-tel', data.trabajador.telefono);
                select.appendChild(option);

                // Seleccionar el nuevo trabajador
                select.value = data.trabajador.matricula;
                mostrarDatosTrabajador(select);
                
                // Cerrar modal y limpiar formulario
                toggleModal('modal-trabajador');
                this.reset();
                
                // Mostrar mensaje de éxito
                mostrarNotificacion('Trabajador guardado correctamente', 'success');
            } else {
                mostrarNotificacion(data.message, 'error');
            }
        })
        .catch(error => {
            mostrarNotificacion('Error al guardar trabajador', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    // GUARDAR BIEN
    document.querySelector('#modal-bien form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin">refresh</span> Guardando...';
        
        fetch('api/guardar_bien.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Agregar a todos los selects de bienes
                const selects = document.querySelectorAll('select[name^="bienes"]');
                selects.forEach(select => {
                    const option = document.createElement('option');
                    option.value = data.bien.id;
                    option.textContent = `${data.bien.identificacion} - ${data.bien.descripcion}`;
                    select.appendChild(option);
                });
                
                // Cerrar modal y limpiar formulario
                toggleModal('modal-bien');
                this.reset();
                
                // Mostrar mensaje de éxito
                mostrarNotificacion('Bien guardado correctamente', 'success');
            } else {
                mostrarNotificacion(data.message, 'error');
            }
        })
        .catch(error => {
            mostrarNotificacion('Error al guardar bien', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    // SISTEMA DE NOTIFICACIONES
    function mostrarNotificacion(mensaje, tipo) {
        const notif = document.createElement('div');
        notif.className = `fixed top-20 right-4 z-[200] px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
            tipo === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notif.innerHTML = `
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined">${tipo === 'success' ? 'check_circle' : 'error'}</span>
                <span class="font-medium">${mensaje}</span>
            </div>
        `;
        
        document.body.appendChild(notif);
        
        // Animar entrada
        setTimeout(() => {
            notif.style.transform = 'translateX(0)';
        }, 10);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            notif.style.transform = 'translateX(150%)';
            setTimeout(() => notif.remove(), 300);
        }, 3000);
    }

    // ANIMACIÓN DE SPIN
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
    `;
    document.head.appendChild(style);

    // Smooth scroll para navegación de tabs
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>

</body>
</html>