<?php
// generador_documentos.php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;

$db = Database::getInstance();
$pdo = $db->getConnection();

$trabajadorRepo = new MySQLTrabajadorRepository($pdo);
$bienRepo = new MySQLBienRepository($pdo);

$trabajadores = $trabajadorRepo->getAll();
$bienes = $bienRepo->getAll();

$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
$tipo_mensaje = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : null;
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMSS - Generador de Documentos</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        .hidden { display: none; }
        .suggestions-box { position: absolute; background: white; border: 1px solid #ccc; width: 100%; z-index: 50; max-height: 200px; overflow-y: auto; }
    </style>
</head>
<body class="bg-gray-100 pb-20">

    <header class="bg-[#247528] text-white p-4 shadow-md">
        <div class="max-w-6xl mx-auto flex items-center gap-4">
            <span class="material-symbols-outlined text-4xl">health_and_safety</span>
            <h1 class="text-xl font-bold">IMSS Control de Bienes - Generador Oficial</h1>
        </div>
    </header>

    <main class="max-w-5xl mx-auto mt-8 px-4">
        <?php if ($mensaje): ?>
            <div class="mb-6 p-4 rounded border <?php echo $tipo_mensaje === 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form action="procesar_pdf.php" method="POST" id="pdfForm" class="space-y-6">
            
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">description</span> Seleccione el tipo de documento
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">
                        <input type="radio" name="tipo_documento" value="salida" class="text-green-700" required onchange="gestionarSecciones(this.value)">
                        <span class="ml-2 font-medium">Constancia de Salida (CBM-2)</span>
                    </label>
                    <label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">
                        <input type="radio" name="tipo_documento" value="resguardo" class="text-green-700" onchange="gestionarSecciones(this.value)">
                        <span class="ml-2 font-medium">Formato de Resguardo (CMB-3)</span>
                    </label>
                    <label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">
                        <input type="radio" name="tipo_documento" value="prestamo" class="text-green-700" onchange="gestionarSecciones(this.value)">
                        <span class="ml-2 font-medium">Constancia de Préstamo (CBM-9)</span>
                    </label>
                </div>
            </div>

            <div id="seccion-general" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h2 class="text-lg font-bold mb-4">Datos del Trabajador</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700">Identificación presentada:</label>
                        <select name="identificacion_tipo" id="identificacion_tipo" class="w-full mt-1 rounded border-gray-300" onchange="actualizarInterfazIdentificacion()">
                            <option value="">Selecciona una opción</option>
                            <option value="GAFETE IMSS">GAFETE INSTITUTO MEXICANO DEL SEGURO SOCIAL</option>
                            <option value="INE">CREDENCIAL INSTITUTO NACIONAL ELECTORAL</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700">Nombre del trabajador:</label>
                        <select name="trabajador_id" id="trabajador_id" class="w-full mt-1 rounded border-gray-300" required onchange="autoLlenarTrabajador()">
                            <option value="">-- Seleccionar Trabajador --</option>
                            <?php foreach ($trabajadores as $t): ?>
                                <option value="<?php echo $t->getId(); ?>" 
                                    data-nombre="<?php echo htmlspecialchars($t->getNombre()); ?>"
                                    data-institucion="<?php echo htmlspecialchars($t->getInstitucion()); ?>"
                                    data-adscripcion="<?php echo htmlspecialchars($t->getAdscripcion()); ?>"
                                    data-matricula="<?php echo htmlspecialchars($t->getMatricula()); ?>"
                                    data-telefono="<?php echo htmlspecialchars($t->getTelefono()); ?>">
                                    <?php echo htmlspecialchars($t->getNombre()); ?> (<?php echo $t->getMatricula(); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700">Institución:</label>
                        <input type="text" name="institucion" id="institucion" class="w-full mt-1 rounded border-gray-300 bg-gray-50">
                    </div>
                    <div>
                        <label id="label-adscripcion" class="block text-sm font-bold text-gray-700">Adscripción:</label>
                        <input type="text" name="adscripcion" id="adscripcion" class="w-full mt-1 rounded border-gray-300 bg-gray-50">
                    </div>
                    <div>
                        <label id="label-matricula" class="block text-sm font-bold text-gray-700">Matrícula:</label>
                        <input type="text" name="matricula" id="matricula" class="w-full mt-1 rounded border-gray-300 bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700">Teléfono:</label>
                        <input type="text" name="telefono" id="telefono" class="w-full mt-1 rounded border-gray-300 bg-gray-50">
                    </div>
                </div>
            </div>

            <div id="seccion-bienes" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Equipos / Bienes</h2>
                    <button type="button" onclick="agregarBien()" class="bg-[#247528] text-white px-4 py-1 rounded hover:bg-green-800 text-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">add_box</span> Agregar Bien
                    </button>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700">Naturaleza del equipo:</label>
                    <select name="naturaleza_bienes" class="w-full mt-1 rounded border-gray-300" required>
                        <option value="BC">BC - Bien de consumo</option>
                        <option value="BMC">BMC - Bien mueble capitalizable</option>
                        <option value="BMNC">BMNC - Bien mueble no capitalizable</option>
                        <option value="BPS">BPS - Bien propiedad solicitante</option>
                    </select>
                </div>

                <div id="contenedor-bienes" class="space-y-3">
                    </div>
            </div>

            <div id="opcion-salida" class="hidden bg-green-50 p-6 rounded-lg border border-green-200 space-y-4">
                <h2 class="font-bold text-green-800 border-b border-green-200 pb-2 text-lg">Detalles de Salida</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold">Área de salida:</label>
                        <input type="text" name="area_origen" class="w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-bold">Propósito / Destino:</label>
                        <input type="text" name="destino" class="w-full rounded border-gray-300">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-bold">Sujeto a devolución:</label>
                        <div class="flex gap-4 mt-2">
                            <label><input type="radio" name="sujeto_devolucion" value="si" checked onclick="toggleFechaDev(true)"> Sí</label>
                            <label><input type="radio" name="sujeto_devolucion" value="no" onclick="toggleFechaDev(false)"> No</label>
                        </div>
                    </div>
                    <div id="div-fecha-dev" class="col-span-2">
                        <label class="block text-sm font-bold text-red-700">Fecha de devolución:</label>
                        <input type="date" name="fecha_devolucion" class="w-full rounded border-gray-300">
                    </div>
                </div>
            </div>

            <div id="opcion-resguardo" class="hidden bg-blue-50 p-6 rounded-lg border border-blue-200 space-y-4">
                <h2 class="font-bold text-blue-800 border-b border-blue-200 pb-2 text-lg">Detalles de Resguardo</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold">Folio:</label>
                        <input type="text" name="folio_resguardo" class="w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-bold">Lugar y Fecha:</label>
                        <input type="text" name="lugar_fecha_resguardo" class="w-full rounded border-gray-300" placeholder="Oaxaca de Juárez, Oax. a...">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-blue-800">Nombre del que entrega:</label>
                        <input type="text" name="recibe_resguardo" class="w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-blue-800">Cargo de quien entrega:</label>
                        <input type="text" name="entrega_resguardo" class="w-full rounded border-gray-300">
                    </div>
                </div>
            </div>

            <div id="opcion-prestamo" class="hidden bg-orange-50 p-6 rounded-lg border border-orange-200 space-y-4">
                <h2 class="font-bold text-orange-800 border-b border-orange-200 pb-2 text-lg">Detalles de Préstamo</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold">Departamento en permanencia:</label>
                        <input type="text" name="departamento_per" class="w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-orange-800">Matrícula Coordinación:</label>
                        <input type="text" name="matricula_coordinacion" class="w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-orange-800">Matrícula Administrativo:</label>
                        <input type="text" name="matricula_administrativo" class="w-full rounded border-gray-300">
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-bold">Lugar y Fecha de Emisión (Para el PDF):</label>
                    <input type="date" name="lugar_fecha" class="w-full rounded border-gray-300" required>
                </div>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-lg z-50">
                <div class="max-w-5xl mx-auto flex justify-between gap-4">
                    <a href="menu.php" class="px-6 py-2 bg-gray-200 rounded font-bold hover:bg-gray-300">Volver al menú</a>
                    <button type="submit" class="px-8 py-2 bg-[#247528] text-white rounded font-bold hover:bg-green-800 flex items-center gap-2">
                        <span class="material-symbols-outlined">picture_as_pdf</span> GENERAR PDF
                    </button>
                </div>
            </div>

        </form>
    </main>

    <script>
        const listaBienesOriginal = <?php echo json_encode(array_map(function($b) {
            return ['id' => $b->getId(), 'identificacion' => $b->getIdentificacion(), 'descripcion' => $b->getDescripcion()];
        }, $bienes)); ?>;

        let contadorBienes = 0;

        function gestionarSecciones(tipo) {
            ['salida', 'resguardo', 'prestamo'].forEach(s => {
                document.getElementById('opcion-' + s).classList.add('hidden');
            });
            document.getElementById('opcion-' + tipo).classList.remove('hidden');
        }

        function actualizarInterfazIdentificacion() {
            const tipo = document.getElementById('identificacion_tipo').value;
            const lMat = document.getElementById('label-matricula');
            const lAds = document.getElementById('label-adscripcion');
            const inst = document.getElementById('institucion');

            if (tipo === 'INE') {
                lMat.innerText = "NIE:";
                lAds.innerText = "Lugar de procedencia:";
                inst.value = "";
                inst.readOnly = false;
            } else {
                lMat.innerText = "Matrícula:";
                lAds.innerText = "Adscripción:";
                inst.value = "INSTITUTO MEXICANO DEL SEGURO SOCIAL";
                inst.readOnly = true;
            }
        }

        function autoLlenarTrabajador() {
            const select = document.getElementById('trabajador_id');
            const opt = select.options[select.selectedIndex];
            if (!opt.value) return;

            document.getElementById('adscripcion').value = opt.dataset.adscripcion;
            document.getElementById('matricula').value = opt.dataset.matricula;
            document.getElementById('telefono').value = opt.dataset.telefono;
            if (document.getElementById('identificacion_tipo').value !== 'INE') {
                document.getElementById('institucion').value = opt.dataset.institucion;
            }
        }

        function agregarBien() {
            contadorBienes++;
            const div = document.createElement('div');
            div.className = "flex gap-2 items-end p-3 bg-gray-50 rounded border border-gray-200 relative";
            div.innerHTML = `
                <div class="flex-grow">
                    <label class="block text-xs font-bold text-gray-600">Seleccionar Bien:</label>
                    <select name="bienes[${contadorBienes}][bien_id]" class="w-full text-sm rounded border-gray-300" required>
                        <option value="">-- Seleccionar --</option>
                        ${listaBienesData = listaBienesOriginal.map(b => `<option value="${b.id}">${b.identificacion} - ${b.descripcion}</option>`).join('')}
                    </select>
                </div>
                <div class="w-20">
                    <label class="block text-xs font-bold text-gray-600">Cant:</label>
                    <input type="number" name="bienes[${contadorBienes}][cantidad]" value="1" min="1" class="w-full text-sm rounded border-gray-300">
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 pb-1">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            `;
            document.getElementById('contenedor-bienes').appendChild(div);
        }

        function toggleFechaDev(mostrar) {
            document.getElementById('div-fecha-dev').style.display = mostrar ? 'block' : 'none';
        }

        // Inicializar un bien
        window.onload = agregarBien;
    </script>
</body>
</html>