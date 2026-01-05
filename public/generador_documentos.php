<?php
// generador_documentos.php - Formulario para generar documentos PDF
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;

// Conexión a base de datos
$db = Database::getInstance();
$pdo = $db->getConnection();

$trabajadorRepo = new MySQLTrabajadorRepository($pdo);
$bienRepo = new MySQLBienRepository($pdo);

// Obtener listas para los selectores
$trabajadores = $trabajadorRepo->getAll();
$bienes = $bienRepo->getAll();

// Inicializar mensaje
$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
$tipo_mensaje = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : null;
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>IMSS - Generador de Documentos PDF</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans:wght@400;500;700&display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
                    },
                    fontFamily: {
                        "display": ["Inter", "Noto Sans", "sans-serif"],
                        "body": ["Inter", "Noto Sans", "sans-serif"],
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "1rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.filled {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .hidden { display: none; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-imss-dark dark:text-white font-display antialiased overflow-x-hidden flex flex-col min-h-screen">
    <!-- TopNavBar -->
    <header class="sticky top-0 z-50 bg-white dark:bg-[#1e2a1e] border-b border-imss-border dark:border-[#2a382a] shadow-sm">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center size-10 rounded-md bg-primary/10 text-primary">
                        <span class="material-symbols-outlined text-3xl">health_and_safety</span>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-lg font-bold leading-tight tracking-tight text-imss-dark dark:text-white">IMSS Control de Bienes</h1>
                        <span class="text-xs font-medium text-imss-gray dark:text-gray-400">Generador de Documentos PDF</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden lg:flex flex-col items-end">
                        <span class="text-sm font-bold text-imss-dark dark:text-white">Sistema Administrativo</span>
                        <span class="text-xs text-imss-gray dark:text-gray-400">Generación de PDFs</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow w-full max-w-[1200px] mx-auto px-4 sm:px-6 py-6 pb-24">
        
        <!-- Breadcrumbs -->
        <nav aria-label="Breadcrumb" class="flex mb-6">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="inline-flex items-center">
                    <a class="inline-flex items-center text-sm font-medium text-imss-gray hover:text-primary dark:text-gray-400" href="#">
                        <span class="material-symbols-outlined text-[18px] mr-1">home</span>
                        Inicio
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <span class="material-symbols-outlined text-imss-gray text-lg mx-1">chevron_right</span>
                        <span class="text-sm font-medium text-primary">Generador de PDFs</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Heading -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-bold text-imss-dark dark:text-white tracking-tight">Generador de Documentos PDF</h2>
                <p class="mt-2 text-imss-gray dark:text-gray-400 max-w-2xl">Seleccione el tipo de documento y complete los datos para generar el PDF oficial.</p>
            </div>
        </div>

        <?php if ($mensaje): ?>
        <!-- Mensaje de resultado -->
        <div class="mb-6 p-4 rounded-lg <?php echo $tipo_mensaje === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-green-50 text-green-800 border border-green-200'; ?>">
            <div class="flex items-center">
                <span class="material-symbols-outlined mr-2"><?php echo $tipo_mensaje === 'error' ? 'error' : 'check_circle'; ?></span>
                <span><?php echo htmlspecialchars($mensaje); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="procesar_pdf.php" method="POST" id="pdfForm" class="space-y-8">
            
            <!-- SECCIÓN 1: TIPO DE DOCUMENTO -->
            <section class="scroll-mt-32">
                <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 bg-gray-50/50 dark:bg-white/5">
                        <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">description</span>
                            Tipo de Documento
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-3">Seleccione el formato <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="relative flex flex-col p-4 border-2 border-imss-border rounded-lg cursor-pointer hover:border-primary hover:bg-primary/5 transition-all">
                                    <input type="radio" name="tipo_documento" value="prestamo" class="peer sr-only" required onchange="mostrarSeccion(this.value)">
                                    <div class="peer-checked:border-primary peer-checked:bg-primary/10 absolute inset-0 rounded-lg border-2"></div>
                                    <div class="flex items-center gap-3 relative">
                                        <span class="material-symbols-outlined text-3xl text-primary">library_books</span>
                                        <div>
                                            <div class="font-semibold">Préstamo</div>
                                            <div class="text-xs text-imss-gray">CBM-9</div>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="relative flex flex-col p-4 border-2 border-imss-border rounded-lg cursor-pointer hover:border-primary hover:bg-primary/5 transition-all">
                                    <input type="radio" name="tipo_documento" value="resguardo" class="peer sr-only" required onchange="mostrarSeccion(this.value)">
                                    <div class="peer-checked:border-primary peer-checked:bg-primary/10 absolute inset-0 rounded-lg border-2"></div>
                                    <div class="flex items-center gap-3 relative">
                                        <span class="material-symbols-outlined text-3xl text-primary">security</span>
                                        <div>
                                            <div class="font-semibold">Resguardo</div>
                                            <div class="text-xs text-imss-gray">CMB-3</div>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="relative flex flex-col p-4 border-2 border-imss-border rounded-lg cursor-pointer hover:border-primary hover:bg-primary/5 transition-all">
                                    <input type="radio" name="tipo_documento" value="salida" class="peer sr-only" required onchange="mostrarSeccion(this.value)">
                                    <div class="peer-checked:border-primary peer-checked:bg-primary/10 absolute inset-0 rounded-lg border-2"></div>
                                    <div class="flex items-center gap-3 relative">
                                        <span class="material-symbols-outlined text-3xl text-primary">exit_to_app</span>
                                        <div>
                                            <div class="font-semibold">Salida</div>
                                            <div class="text-xs text-imss-gray">CBM-2</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 2: DATOS GENERALES (Común para todos) -->
            <section class="scroll-mt-32" id="seccion-general">
                <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 bg-gray-50/50 dark:bg-white/5">
                        <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">person</span>
                            Datos del Trabajador
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Seleccionar Trabajador <span class="text-red-500">*</span></label>
                            <select name="trabajador_id" id="trabajador_id" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white" required onchange="cargarDatosTrabajador()">
                                <option value="">-- Seleccione un trabajador --</option>
                                <?php foreach ($trabajadores as $t): ?>
                                <option value="<?php echo $t->getId(); ?>" 
                                    data-nombre="<?php echo htmlspecialchars($t->getNombre()); ?>"
                                    data-cargo="<?php echo htmlspecialchars($t->getCargo()); ?>"
                                    data-adscripcion="<?php echo htmlspecialchars($t->getAdscripcion()); ?>"
                                    data-matricula="<?php echo htmlspecialchars($t->getMatricula()); ?>"
                                    data-identificacion="<?php echo htmlspecialchars($t->getIdentificacion()); ?>"
                                    data-telefono="<?php echo htmlspecialchars($t->getTelefono()); ?>">
                                    <?php echo htmlspecialchars($t->getNombre()); ?> - <?php echo htmlspecialchars($t->getMatricula()); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Nombre Completo</label>
                            <input type="text" id="nombre" name="nombre" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white bg-gray-50" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Cargo</label>
                            <input type="text" id="cargo" name="cargo" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white bg-gray-50" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Adscripción</label>
                            <input type="text" id="adscripcion" name="adscripcion" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white bg-gray-50" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Matrícula</label>
                            <input type="text" id="matricula" name="matricula" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white bg-gray-50" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Identificación</label>
                            <input type="text" id="identificacion" name="identificacion" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white bg-gray-50" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white bg-gray-50" readonly>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 3: BIENES -->
            <section class="scroll-mt-32" id="seccion-bienes">
                <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 bg-gray-50/50 dark:bg-white/5 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">inventory_2</span>
                                Bienes Involucrados
                            </h3>
                            <p class="text-xs text-imss-gray dark:text-gray-400 mt-0.5">Seleccione los bienes para este documento.</p>
                        </div>
                        <button type="button" onclick="agregarBien()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <span class="material-symbols-outlined text-lg mr-2">add</span>
                            Agregar Bien
                        </button>
                    </div>
                    <div class="p-6">
                        <div id="lista-bienes" class="space-y-4">
                            <!-- Los bienes se agregarán dinámicamente aquí -->
                        </div>
                        <div id="mensaje-sin-bienes" class="text-center py-8 text-imss-gray">
                            <span class="material-symbols-outlined text-5xl mb-2 opacity-50">inventory_2</span>
                            <p>No hay bienes agregados. Haga clic en "Agregar Bien" para comenzar.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 4: CAMPOS ESPECÍFICOS DE PRÉSTAMO -->
            <section class="scroll-mt-32 hidden" id="seccion-prestamo">
                <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 bg-gray-50/50 dark:bg-white/5">
                        <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">schedule</span>
                            Datos del Préstamo
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Fecha de Emisión <span class="text-red-500">*</span></label>
                            <input type="date" name="fecha_emision_prestamo" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Lugar y Fecha <span class="text-red-500">*</span></label>
                            <input type="text" name="lugar_fecha_prestamo" placeholder="Ej: Oaxaca, Oax. a 5 de enero de 2026" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Nota (Estado Físico de los Bienes)</label>
                            <textarea name="nota_prestamo" rows="2" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white" placeholder="Describa el estado físico de los bienes..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Matrícula Autoriza</label>
                            <input type="text" name="matricula_autoriza" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Matrícula Recibe</label>
                            <input type="text" name="matricula_recibe" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 5: CAMPOS ESPECÍFICOS DE RESGUARDO -->
            <section class="scroll-mt-32 hidden" id="seccion-resguardo">
                <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 bg-gray-50/50 dark:bg-white/5">
                        <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">assignment</span>
                            Datos del Resguardo
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Folio</label>
                            <input type="text" name="folio_resguardo" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Lugar y Fecha <span class="text-red-500">*</span></label>
                            <input type="text" name="lugar_fecha_resguardo" placeholder="Ej: Oaxaca, Oax. a 5 de enero de 2026" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Notas Adicionales</label>
                            <textarea name="notas_resguardo" rows="3" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white" placeholder="Información adicional sobre el resguardo..."></textarea>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 6: CAMPOS ESPECÍFICOS DE SALIDA -->
            <section class="scroll-mt-32 hidden" id="seccion-salida">
                <div class="bg-white dark:bg-[#1e2a1e] rounded-xl shadow-sm border border-imss-border dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-imss-border dark:border-gray-700 bg-gray-50/50 dark:bg-white/5">
                        <h3 class="text-lg font-bold text-imss-dark dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">local_shipping</span>
                            Datos de la Salida
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Área de Origen <span class="text-red-500">*</span></label>
                            <input type="text" name="area_origen" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Destino (Para su...) <span class="text-red-500">*</span></label>
                            <input type="text" name="destino" placeholder="Ej: reparación, mantenimiento, etc." class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Lugar y Fecha <span class="text-red-500">*</span></label>
                            <input type="text" name="lugar_fecha_salida" placeholder="Ej: Oaxaca, Oax. a 5 de enero de 2026" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        </div>

                        <div class="flex items-center gap-4">
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200">Sujeto a Devolución</label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="sujeto_devolucion" value="SI" class="form-radio text-primary focus:ring-primary" checked>
                                    <span class="ml-2">Sí</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="sujeto_devolucion" value="NO" class="form-radio text-primary focus:ring-primary">
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                        </div>

                        <div id="fecha-devolucion-container">
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Fecha de Devolución</label>
                            <input type="text" name="fecha_devolucion" placeholder="Día: ___" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-imss-dark dark:text-gray-200 mb-1">Observaciones al Estado Físico</label>
                            <textarea name="observaciones_salida" rows="2" class="block w-full px-3 py-2.5 border-imss-border focus:ring-primary focus:border-primary sm:text-sm rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white" placeholder="Describa el estado físico de los bienes..."></textarea>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Botones de Acción -->
            <div class="fixed bottom-0 left-0 w-full bg-white dark:bg-[#1e2a1e] border-t border-imss-border dark:border-gray-700 shadow-lg z-50">
                <div class="max-w-[1200px] mx-auto px-4 sm:px-6 h-20 flex items-center justify-between">
                    <button type="button" onclick="window.history.back()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-imss-dark bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex items-center px-6 py-2 text-sm font-bold text-white bg-primary rounded-lg shadow-sm hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transform transition hover:scale-[1.02]">
                        <span class="material-symbols-outlined text-[20px] mr-2">picture_as_pdf</span>
                        Generar PDF
                    </button>
                </div>
            </div>

        </form>
    </main>

    <script>
        // Datos de bienes para el selector
        const bienes = <?php echo json_encode(array_map(function($b) {
            return [
                'id' => $b->getId(),
                'identificacion' => $b->getIdentificacion(),
                'descripcion' => $b->getDescripcion(),
                'marca' => $b->getMarca(),
                'modelo' => $b->getModelo(),
                'serie' => $b->getSerie(),
                'naturaleza' => $b->getNaturaleza()
            ];
        }, $bienes)); ?>;

        let contadorBienes = 0;

        function mostrarSeccion(tipo) {
            // Ocultar todas las secciones específicas
            document.getElementById('seccion-prestamo').classList.add('hidden');
            document.getElementById('seccion-resguardo').classList.add('hidden');
            document.getElementById('seccion-salida').classList.add('hidden');
            
            // Mostrar la sección correspondiente
            document.getElementById('seccion-' + tipo).classList.remove('hidden');
            
            // Mostrar secciones comunes
            document.getElementById('seccion-general').classList.remove('hidden');
            document.getElementById('seccion-bienes').classList.remove('hidden');
        }

        function cargarDatosTrabajador() {
            const select = document.getElementById('trabajador_id');
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                document.getElementById('nombre').value = option.dataset.nombre || '';
                document.getElementById('cargo').value = option.dataset.cargo || '';
                document.getElementById('adscripcion').value = option.dataset.adscripcion || '';
                document.getElementById('matricula').value = option.dataset.matricula || '';
                document.getElementById('identificacion').value = option.dataset.identificacion || '';
                document.getElementById('telefono').value = option.dataset.telefono || '';
            } else {
                document.getElementById('nombre').value = '';
                document.getElementById('cargo').value = '';
                document.getElementById('adscripcion').value = '';
                document.getElementById('matricula').value = '';
                document.getElementById('identificacion').value = '';
                document.getElementById('telefono').value = '';
            }
        }

        function agregarBien() {
            contadorBienes++;
            const listaBienes = document.getElementById('lista-bienes');
            const mensajeSinBienes = document.getElementById('mensaje-sin-bienes');
            
            mensajeSinBienes.classList.add('hidden');
            
            const divBien = document.createElement('div');
            divBien.className = 'border border-imss-border rounded-lg p-4 bg-gray-50 dark:bg-gray-800/30';
            divBien.id = 'bien-' + contadorBienes;
            
            divBien.innerHTML = `
                <div class="flex items-start justify-between mb-3">
                    <h4 class="font-semibold text-imss-dark dark:text-white">Bien #${contadorBienes}</h4>
                    <button type="button" onclick="eliminarBien(${contadorBienes})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium mb-1">Seleccionar Bien <span class="text-red-500">*</span></label>
                        <select name="bienes[${contadorBienes}][bien_id]" class="block w-full px-3 py-2 border-imss-border rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white" required onchange="cargarDatosBien(${contadorBienes}, this.value)">
                            <option value="">-- Seleccione un bien --</option>
                            ${bienes.map(b => `<option value="${b.id}">${b.identificacion} - ${b.descripcion}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Cantidad</label>
                        <input type="number" name="bienes[${contadorBienes}][cantidad]" value="1" min="1" class="block w-full px-3 py-2 border-imss-border rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Naturaleza</label>
                        <input type="text" id="naturaleza-${contadorBienes}" class="block w-full px-3 py-2 border-imss-border rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white bg-gray-100" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Marca/Modelo</label>
                        <input type="text" id="marca-modelo-${contadorBienes}" class="block w-full px-3 py-2 border-imss-border rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white bg-gray-100" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Serie</label>
                        <input type="text" id="serie-${contadorBienes}" class="block w-full px-3 py-2 border-imss-border rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white bg-gray-100" readonly>
                    </div>
                </div>
            `;
            
            listaBienes.appendChild(divBien);
        }

        function eliminarBien(id) {
            const divBien = document.getElementById('bien-' + id);
            if (divBien) {
                divBien.remove();
            }
            
            // Verificar si quedan bienes
            const listaBienes = document.getElementById('lista-bienes');
            if (listaBienes.children.length === 0) {
                document.getElementById('mensaje-sin-bienes').classList.remove('hidden');
            }
        }

        function cargarDatosBien(contadorId, bienId) {
            const bien = bienes.find(b => b.id == bienId);
            if (bien) {
                document.getElementById('naturaleza-' + contadorId).value = bien.naturaleza || '';
                document.getElementById('marca-modelo-' + contadorId).value = (bien.marca && bien.modelo) ? `${bien.marca} / ${bien.modelo}` : '';
                document.getElementById('serie-' + contadorId).value = bien.serie || '';
            }
        }

        // Validación del formulario
        document.getElementById('pdfForm').addEventListener('submit', function(e) {
            const tipoDoc = document.querySelector('input[name="tipo_documento"]:checked');
            if (!tipoDoc) {
                e.preventDefault();
                alert('Por favor seleccione el tipo de documento');
                return false;
            }

            const trabajadorId = document.getElementById('trabajador_id').value;
            if (!trabajadorId) {
                e.preventDefault();
                alert('Por favor seleccione un trabajador');
                return false;
            }

            const listaBienes = document.getElementById('lista-bienes');
            if (listaBienes.children.length === 0) {
                e.preventDefault();
                alert('Por favor agregue al menos un bien');
                return false;
            }

            return true;
        });

        // Manejo de fecha de devolución en salida
        document.querySelectorAll('input[name="sujeto_devolucion"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const container = document.getElementById('fecha-devolucion-container');
                if (this.value === 'NO') {
                    container.classList.add('hidden');
                } else {
                    container.classList.remove('hidden');
                }
            });
        });

        // Inicializar con fecha actual en los campos de fecha
        const hoy = new Date().toISOString().split('T')[0];
        document.querySelector('input[name="fecha_emision_prestamo"]').value = hoy;
    </script>
</body>
</html>