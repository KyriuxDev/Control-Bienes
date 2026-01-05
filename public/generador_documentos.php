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

$trabajadores = $trabajadorRepo->getAll();
$bienesCatalogo = $bienRepo->getAll();
?>
<!DOCTYPE html>
<html lang="es" class="light">
<head>
    <meta charset="UTF-8">
    <title>IMSS - Generador de Documentos</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: { extend: { colors: { "primary": "#247528", "imss-border": "#dde4dd" } } }
        }
    </script>
</head>
<body class="bg-[#f6f8f6] font-sans text-gray-900 pb-20">

<header class="bg-white border-b border-imss-border p-4 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto flex items-center gap-3">
        <span class="material-symbols-outlined text-primary text-4xl">health_and_safety</span>
        <h1 class="text-xl font-bold">Generación de Documentos IMSS</h1>
    </div>
</header>

<main class="max-w-5xl mx-auto mt-8 px-4">
    <form action="procesar_pdf.php" method="POST" class="space-y-6">
        
        <div class="bg-white p-6 rounded-xl shadow-sm border border-imss-border">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">description</span> 1. Selección de Documentos
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-green-50">
                    <input type="checkbox" name="docs_a_generar[]" value="resguardo" class="rounded text-primary" checked>
                    <span class="ml-3 font-bold">Resguardo Individual (CMB-3)</span>
                </label>
                </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-imss-border">
            <h2 class="text-lg font-bold mb-4">2. Datos del Trabajador (Resguardante)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Seleccionar Trabajador:</label>
                    <select name="trabajador_id" id="trabajador_id" class="w-full rounded-lg border-gray-300" required onchange="mostrarDatosTrabajador(this)">
                        <option value="">-- Seleccione --</option>
                        <?php foreach($trabajadores as $t): ?>
                            <option value="<?php echo $t->getId(); ?>" 
                                data-mat="<?php echo htmlspecialchars($t->getMatricula()); ?>"
                                data-cargo="<?php echo htmlspecialchars($t->getCargo()); ?>"
                                data-ads="<?php echo htmlspecialchars($t->getAdscripcion()); ?>"
                                data-tel="<?php echo htmlspecialchars($t->getTelefono()); ?>">
                                <?php echo htmlspecialchars($t->getNombre()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="panel-datos" class="md:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg border border-dashed border-primary/30 hidden">
                    <div><p class="text-[10px] font-bold text-imss-gray uppercase tracking-widest">Matrícula</p><p id="val-mat" class="font-bold"></p></div>
                    <div><p class="text-[10px] font-bold text-imss-gray uppercase tracking-widest">Cargo</p><p id="val-cargo"></p></div>
                    <div><p class="text-[10px] font-bold text-imss-gray uppercase tracking-widest">Adscripción</p><p id="val-ads"></p></div>
                    <div><p class="text-[10px] font-bold text-imss-gray uppercase tracking-widest">Teléfono</p><p id="val-tel"></p></div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-imss-border">
            <h2 class="text-lg font-bold mb-4">3. Detalles del Resguardo</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold">Folio del Reporte:</label>
                    <input type="text" name="folio_resguardo" placeholder="Ej. 2026/054" class="w-full rounded-lg border-gray-300">
                </div>
                <div> 
                    <label class="block text-sm font-bold">Lugar y Fecha:</label>
                    <?php
                        // Arreglo de meses para PHP 5.6
                        $meses = array("1"=>"enero", "2"=>"febrero", "3"=>"marzo", "4"=>"abril", "5"=>"mayo", "6"=>"junio", "7"=>"julio", "8"=>"agosto", "9"=>"septiembre", "10"=>"octubre", "11"=>"noviembre", "12"=>"diciembre");
                        $fecha_formateada = "Oaxaca de Juárez, Oaxaca, " . date('j') . " de " . $meses[date('n')] . " de " . date('Y');
                    ?>
                    <input type="text" 
                        name="lugar_fecha_resguardo" 
                        value="<?php echo $fecha_formateada; ?>" 
                        class="w-full rounded-lg border-gray-300 bg-gray-100 cursor-not-allowed" 
                        readonly>
                </div>
                <div>
                    <label class="block text-sm font-bold text-blue-800">Nombre de quien entrega:</label>
                    <input type="text" name="recibe_resguardo" class="w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-bold text-blue-800">Cargo de quien entrega:</label>
                    <input type="text" name="entrega_resguardo" class="w-full rounded-lg border-gray-300">
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-imss-border">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold">4. Bienes</h2>
                <button type="button" onclick="agregarBien()" class="text-primary font-bold">+ Agregar Bien</button>
            </div>
            <div id="contenedor-bienes" class="space-y-3">
                <div class="flex gap-2 p-3 bg-gray-50 rounded border">
                    <select name="bienes[0][bien_id]" class="flex-grow rounded border-gray-300 text-sm" required>
                        <option value="">-- Seleccionar Bien --</option>
                        <?php foreach($bienesCatalogo as $b): ?>
                            <option value="<?php echo $b->getId(); ?>"><?php echo $b->getIdentificacion(); ?> - <?php echo $b->getDescripcion(); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="bienes[0][cantidad]" value="1" min="1" class="w-20 rounded border-gray-300">
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-primary text-white py-4 rounded-xl font-bold shadow-lg hover:bg-green-800">GENERAR DOCUMENTO</button>
    </form>
</main>

<script>
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

    let bIdx = 1;
    function agregarBien() {
        const div = document.createElement('div');
        div.className = "flex gap-2 p-3 bg-gray-50 rounded border";
        div.innerHTML = `
            <select name="bienes[${bIdx}][bien_id]" class="flex-grow rounded border-gray-300 text-sm" required>
                <option value="">-- Seleccionar --</option>
                <?php foreach($bienesCatalogo as $b): echo "<option value='".$b->getId()."'>".$b->getIdentificacion()." - ".$b->getDescripcion()."</option>"; endforeach; ?>
            </select>
            <input type="number" name="bienes[${bIdx}][cantidad]" value="1" class="w-20 rounded border-gray-300">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 font-bold px-2">X</button>
        `;
        document.getElementById('contenedor-bienes').appendChild(div);
        bIdx++;
    }
</script>
</body>
</html>