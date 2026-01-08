// VERSIÓN SIMPLIFICADA Y ROBUSTA - CON OPCIONES GLOBALES
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
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-gray-500">Cantidad:</label>
                    <input type="number" name="bienes[${bIdx}][cantidad]" value="1" min="1" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 text-sm">
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

        // Manejar el campo "Otro" para el estado
        const estadoGeneral = document.getElementById('estado_general');
        const otroEstadoContainer = document.getElementById('otro-estado-container');
        
        if (estadoGeneral && otroEstadoContainer) {
            estadoGeneral.addEventListener('change', function() {
                if (this.value === 'Otro') {
                    otroEstadoContainer.classList.remove('hidden');
                    document.getElementById('estado_otro').required = true;
                } else {
                    otroEstadoContainer.classList.add('hidden');
                    document.getElementById('estado_otro').required = false;
                    document.getElementById('estado_otro').value = '';
                }
            });
        }

        // Manejar submit del formulario de crear bien
        const formBien = document.getElementById('form-bien');
        if (formBien) {
            console.log('✅ Formulario #form-bien encontrado, agregando manejador');
            
            formBien.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('📤 Enviando formulario de bien...');
                
                const formData = new FormData(this);
                
                // Validar descripción
                const descripcion = formData.get('descripcion');
                if (!descripcion || descripcion.trim() === '') {
                    if (typeof mostrarNotificacion === 'function') {
                        mostrarNotificacion('La descripción es obligatoria', 'error');
                    } else {
                        alert('La descripción es obligatoria');
                    }
                    return;
                }
                
                // Deshabilitar botón
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.textContent : 'Crear Registro';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">refresh</span> Guardando...';
                }
                
                fetch('api/guardar_bien.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => {
                    console.log('📥 Respuesta recibida:', r.status);
                    return r.json();
                })
                .then(data => {
                    console.log('📦 Datos recibidos:', data);
                    
                    if (data.success) {
                        // Cerrar modal
                        if (typeof toggleModal === 'function') {
                            toggleModal('modal-bien');
                        }
                        
                        // Mostrar notificación
                        if (typeof mostrarNotificacion === 'function') {
                            mostrarNotificacion('Bien guardado correctamente', 'success');
                        }
                        
                        // Limpiar formulario
                        formBien.reset();
                        
                        // Actualizar catálogo en memoria
                        if (window.APP_DATA && window.APP_DATA.bienesCatalogo && data.bien) {
                            console.log('✅ Agregando bien al catálogo:', data.bien);
                            window.APP_DATA.bienesCatalogo.push(data.bien);
                            window.actualizarDropdownsBienes();
                        } else {
                            // Si no hay APP_DATA, recargar página
                            console.log('⚠️ APP_DATA no disponible, recargando página...');
                            setTimeout(() => location.reload(), 1000);
                        }
                    } else {
                        const mensaje = data.message || 'Error al guardar el bien';
                        if (typeof mostrarNotificacion === 'function') {
                            mostrarNotificacion(mensaje, 'error');
                        } else {
                            alert(mensaje);
                        }
                        console.error('❌ Error del servidor:', mensaje);
                    }
                })
                .catch(error => {
                    console.error('❌ Error en la petición:', error);
                    const mensaje = 'Error de conexión al guardar el bien';
                    if (typeof mostrarNotificacion === 'function') {
                        mostrarNotificacion(mensaje, 'error');
                    } else {
                        alert(mensaje);
                    }
                })
                .finally(() => {
                    // Rehabilitar botón
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                });
            });
        } else {
            console.warn('⚠️ Formulario #form-bien no encontrado');
        }
    }
})();