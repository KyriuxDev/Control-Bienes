let bIdx = 1;

window.agregarFilaBien = function () {
    // Validamos que existan los datos inyectados
    if (!window.APP_DATA || !window.APP_DATA.bienesCatalogo) {
        console.error("Error: APP_DATA.bienesCatalogo no está definido.");
        return;
    }

    const bienesCatalogo = window.APP_DATA.bienesCatalogo;
    const contenedor = document.getElementById('contenedor-bienes');
    const div = document.createElement('div');

    div.className = "bien-row flex gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 items-start hover:shadow-md transition-shadow animate-pulse";

    let optionsHTML = '<option value="">-- Seleccionar Bien --</option>';
    bienesCatalogo.forEach(b => {
        const label = (b.serie || 'BIEN-' + b.id_bien) + ' - ' + b.descripcion;
        optionsHTML += `<option value="${b.id_bien}">${label}</option>`;
    });

    div.innerHTML = `
        <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary flex-shrink-0 mt-1">
            <span class="material-symbols-outlined">inventory</span>
        </div>
        <div class="flex-grow space-y-3">
            <select name="bienes[${bIdx}][id_bien]" class="w-full rounded-lg border-gray-300 text-sm dark:bg-gray-700 dark:text-white" required>
                ${optionsHTML}
            </select>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-gray-500">Cantidad:</label>
                    <input type="number" name="bienes[${bIdx}][cantidad]" value="1" min="1" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 text-sm">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-gray-500">Estado:</label>
                    <input type="text" name="bienes[${bIdx}][estado_fisico]" placeholder="Ej. Bueno" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 text-sm">
                </div>
                <div class="flex items-center gap-2 constancia-only hidden">
                    <label class="text-xs font-bold text-gray-500 flex items-center gap-1">
                        <input type="checkbox" name="bienes[${bIdx}][sujeto_devolucion]" value="1" class="rounded text-primary">
                        Sujeto a devolución
                    </label>
                </div>
            </div>
        </div>
        <button type="button" onclick="this.closest('.bien-row').remove()" class="text-red-500 hover:bg-red-50 p-2 rounded-lg mt-1">
            <span class="material-symbols-outlined">delete</span>
        </button>
    `;

    contenedor.appendChild(div);
    setTimeout(() => div.classList.remove('animate-pulse'), 300);
    
    // Actualizar visibilidad de campos si la función existe
    if (typeof window.updateConstanciaFields === 'function') {
        window.updateConstanciaFields();
    }
    bIdx++;
};