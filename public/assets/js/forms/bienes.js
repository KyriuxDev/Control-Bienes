// VERSIÓN SIMPLIFICADA Y ROBUSTA
(function() {
    'use strict';
    
    console.log('🔵 bienes.js cargado');
    
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
        return seleccionados;
    };

    // Función para actualizar todos los dropdowns de bienes
    window.actualizarDropdownsBienes = function () {
        if (!window.APP_DATA || !window.APP_DATA.bienesCatalogo) {
            console.error('❌ APP_DATA no disponible');
            return;
        }

        const bienesSeleccionados = window.getBienesSeleccionados();
        const selects = document.querySelectorAll('select[name^="bienes["][name$="][id_bien]"]');
        
        selects.forEach(select => {
            const valorActual = select.value;
            
            // Limpiar opciones
            select.innerHTML = '<option value="">-- Seleccionar Bien --</option>';
            
            // Agregar opciones filtrando los ya seleccionados (excepto el valor actual)
            window.APP_DATA.bienesCatalogo.forEach(b => {
                const idBienStr = String(b.id_bien);
                const yaSeleccionado = bienesSeleccionados.includes(idBienStr) && idBienStr !== String(valorActual);
                
                if (!yaSeleccionado) {
                    const label = (b.serie || 'BIEN-' + b.id_bien) + ' - ' + b.descripcion;
                    const option = document.createElement('option');
                    option.value = b.id_bien;
                    option.textContent = label;
                    select.appendChild(option);
                }
            });
            
            // Restaurar valor seleccionado si existe
            if (valorActual) {
                select.value = valorActual;
            }
        });
    };

    // Función para agregar el listener a un select
    function agregarListenerASelect(select) {
        select.addEventListener('change', function() {
            window.actualizarDropdownsBienes();
        });
    }

    window.agregarFilaBien = function () {
        if (!window.APP_DATA || !window.APP_DATA.bienesCatalogo) {
            alert("Error: No hay datos de bienes disponibles");
            return;
        }
        const bienesCatalogo = window.APP_DATA.bienesCatalogo;
        const bienesSeleccionados = window.getBienesSeleccionados();
        const contenedor = document.getElementById('contenedor-bienes');
        
        if (!contenedor) {
            console.error('❌ No se encontró el contenedor de bienes');
            return;
        }
        
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
            agregarListenerASelect(nuevoSelect);
        }
        
        setTimeout(() => div.classList.remove('animate-pulse'), 300);
        
        if (typeof window.updateConstanciaFields === 'function') {
            window.updateConstanciaFields();
        }
        
        bIdx++;
    };

    window.eliminarFilaBien = function (button) {
        button.closest('.bien-row').remove();
        window.actualizarDropdownsBienes();
    };

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar);
    } else {
        inicializar();
    }

    function inicializar() {
        
        const todosLosSelects = document.querySelectorAll('select[name^="bienes["][name$="][id_bien]"]');
        todosLosSelects.forEach(select => {
            agregarListenerASelect(select);
        });
    }
})();