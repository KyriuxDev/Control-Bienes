let bIdx = 1;

// Función para obtener los IDs de bienes ya seleccionados
window.getBienesSeleccionados = function () {
    const selects = document.querySelectorAll('select[name^="bienes["][name$="][id_bien]"]');
    const seleccionados = [];
    selects.forEach(select => {
        if (select.value) {
            seleccionados.push(select.value);
        }
    });
    console.log('Bienes seleccionados:', seleccionados); // Debug
    return seleccionados;
};

// Función para actualizar todos los dropdowns de bienes
window.actualizarDropdownsBienes = function () {
    if (!window.APP_DATA || !window.APP_DATA.bienesCatalogo) {
        console.error('APP_DATA no disponible');
        return;
    }

    console.log('Actualizando dropdowns...'); // Debug
    const bienesSeleccionados = window.getBienesSeleccionados();
    const selects = document.querySelectorAll('select[name^="bienes["][name$="][id_bien]"]');
    
    selects.forEach(select => {
        const valorActual = select.value;
        console.log('Procesando select, valor actual:', valorActual); // Debug
        
        // Limpiar opciones
        select.innerHTML = '<option value="">-- Seleccionar Bien --</option>';
        
        // Agregar opciones filtrando los ya seleccionados (excepto el valor actual)
        window.APP_DATA.bienesCatalogo.forEach(b => {
            const yaSeleccionado = bienesSeleccionados.includes(String(b.id_bien)) && String(b.id_bien) !== valorActual;
            
            if (!yaSeleccionado) {
                const label = (b.serie || 'BIEN-' + b.id_bien) + ' - ' + b.descripcion;
                const option = document.createElement('option');
                option.value = b.id_bien;
                option.textContent = label;
                select.appendChild(option);
            } else {
                console.log('Ocultando bien:', b.id_bien, b.descripcion); // Debug
            }
        });
        
        // Restaurar valor seleccionado si existe
        if (valorActual) {
            select.value = valorActual;
        }
    });
};

window.agregarFilaBien = function () {
    // Validamos que existan los datos inyectados
    if (!window.APP_DATA || !window.APP_DATA.bienesCatalogo) {
        console.error("Error: APP_DATA.bienesCatalogo no está definido.");
        return;
    }

    const bienesCatalogo = window.APP_DATA.bienesCatalogo;
    const bienesSeleccionados = window.getBienesSeleccionados();
    const contenedor = document.getElementById('contenedor-bienes');
    const div = document.createElement('div');

    div.className = "bien-row flex gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 items-start hover:shadow-md transition-shadow animate-pulse";

    // Filtrar bienes ya seleccionados
    let optionsHTML = '<option value="">-- Seleccionar Bien --</option>';
    bienesCatalogo.forEach(b => {
        if (!bienesSeleccionados.includes(String(b.id_bien))) {
            const label = (b.serie || 'BIEN-' + b.id_bien) + ' - ' + b.descripcion;
            optionsHTML += `<option value="${b.id_bien}">${label}</option>`;
        }
    });

    div.innerHTML = `
        <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary flex-shrink-0 mt-1">
            <span class="material-symbols-outlined">inventory</span>
        </div>
        <div class="flex-grow space-y-3">
            <select name="bienes[${bIdx}][id_bien]" class="bien-select w-full rounded-lg border-gray-300 text-sm dark:bg-gray-700 dark:text-white" required>
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
        <button type="button" onclick="eliminarFilaBien(this)" class="text-red-500 hover:bg-red-50 p-2 rounded-lg mt-1">
            <span class="material-symbols-outlined">delete</span>
        </button>
    `;

    contenedor.appendChild(div);
    
    // Agregar evento change al nuevo select
    const nuevoSelect = div.querySelector('select[name^="bienes["][name$="][id_bien]"]');
    if (nuevoSelect) {
        nuevoSelect.addEventListener('change', function() {
            console.log('Select cambiado'); // Debug
            window.actualizarDropdownsBienes();
        });
    }
    
    setTimeout(() => div.classList.remove('animate-pulse'), 300);
    
    // Actualizar visibilidad de campos si la función existe
    if (typeof window.updateConstanciaFields === 'function') {
        window.updateConstanciaFields();
    }
    bIdx++;
};

// Función para eliminar una fila y actualizar dropdowns
window.eliminarFilaBien = function (button) {
    console.log('Eliminando fila'); // Debug
    button.closest('.bien-row').remove();
    window.actualizarDropdownsBienes();
};

// Inicializar eventos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, inicializando eventos de bienes'); // Debug
    
    // Agregar evento change a TODOS los selects de bienes existentes
    const todosLosSelects = document.querySelectorAll('select[name^="bienes["][name$="][id_bien]"]');
    console.log('Selects encontrados:', todosLosSelects.length); // Debug
    
    todosLosSelects.forEach(select => {
        select.addEventListener('change', function() {
            console.log('Select cambiado (desde DOMContentLoaded)'); // Debug
            window.actualizarDropdownsBienes();
        });
    });
});