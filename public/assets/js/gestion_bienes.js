// public/assets/js/gestion_bienes.js - VERSIÓN FINAL CORREGIDA

(function() {
    'use strict';

    // Variables globales
    let todosLosBienes = [];
    let bienesFiltrados = [];
    let paginaActual = 1;
    const itemsPorPagina = 10;

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        cargarBienes();
        inicializarFormulario();
    });

    function cargarBienes() {
        const rows = document.querySelectorAll('.bien-row');
        todosLosBienes = Array.from(rows).map(row => ({
            id: row.dataset.id,
            naturaleza: row.dataset.naturaleza,
            descripcion: row.dataset.descripcion.toLowerCase(),
            marca: row.dataset.marca.toLowerCase(),
            modelo: row.dataset.modelo.toLowerCase(),
            serie: row.dataset.serie.toLowerCase(),
            element: row
        }));
        bienesFiltrados = [...todosLosBienes];
        actualizarVista();
    }

    function inicializarFormulario() {
        const form = document.getElementById('form-bien');
        if (!form) {
            console.error('Formulario #form-bien no encontrado');
            return;
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('📝 Formulario enviado');
            
            const formData = new FormData(this);
            
            // CORRECCIÓN: Obtener el ID directamente del campo hidden
            const idBienInput = document.getElementById('id_bien');
            const idBien = idBienInput ? idBienInput.value : '';
            
            console.log('ID Bien del campo:', idBien);
            console.log('ID Bien está vacío?', idBien === '');
            console.log('ID Bien es null?', idBien === null);
            console.log('ID Bien es undefined?', idBien === undefined);
            
            // Validar descripción
            const descripcion = formData.get('descripcion');
            if (!descripcion || descripcion.trim() === '') {
                mostrarNotificacion('La descripción es obligatoria', 'error');
                return;
            }
            
            // Deshabilitar botón
            const submitBtn = document.getElementById('btn-submit-bien');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">refresh</span> Guardando...';
            
            // CORRECCIÓN CRÍTICA: Determinar la acción basándose en si el ID existe Y NO está vacío
            let url, accion;
            
            if (idBien && idBien.trim() !== '' && idBien !== 'null' && idBien !== 'undefined') {
                // HAY ID válido → ACTUALIZAR
                url = 'api/actualizar_bien.php';
                accion = 'actualizar';
                console.log('✏️ MODO: ACTUALIZAR (ID existe:', idBien + ')');
            } else {
                // NO hay ID → CREAR NUEVO
                url = 'api/guardar_bien.php';
                accion = 'crear';
                console.log('🆕 MODO: CREAR NUEVO (sin ID)');
            }
            
            console.log('URL seleccionada:', url);
            console.log('Acción:', accion);
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(r => {
                console.log('Respuesta recibida:', r.status);
                if (!r.ok) {
                    throw new Error('Error HTTP: ' + r.status);
                }
                return r.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    cerrarModalBien();
                    
                    if (accion === 'crear') {
                        mostrarNotificacion('Bien creado correctamente', 'success');
                    } else {
                        mostrarNotificacion('Bien actualizado correctamente', 'success');
                    }
                    
                    // Recargar página para mostrar cambios
                    setTimeout(() => location.reload(), 1000);
                } else {
                    mostrarNotificacion(data.message || 'Error en la operación', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('Error de conexión: ' + error.message, 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    }

    // Funciones de filtrado y ordenamiento
    window.filtrarBienes = function() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const naturalezaFilter = document.getElementById('filter-naturaleza').value;
        
        bienesFiltrados = todosLosBienes.filter(bien => {
            const matchSearch = !searchTerm || 
                bien.descripcion.includes(searchTerm) ||
                bien.marca.includes(searchTerm) ||
                bien.modelo.includes(searchTerm) ||
                bien.serie.includes(searchTerm);
            
            const matchNaturaleza = !naturalezaFilter || bien.naturaleza === naturalezaFilter;
            
            return matchSearch && matchNaturaleza;
        });
        
        paginaActual = 1;
        actualizarVista();
    };

    window.ordenarBienes = function() {
        const ordenValue = document.getElementById('filter-orden').value;
        
        bienesFiltrados.sort((a, b) => {
            switch(ordenValue) {
                case 'id_asc':
                    return parseInt(a.id) - parseInt(b.id);
                case 'id_desc':
                    return parseInt(b.id) - parseInt(a.id);
                case 'descripcion_asc':
                    return a.descripcion.localeCompare(b.descripcion);
                case 'descripcion_desc':
                    return b.descripcion.localeCompare(a.descripcion);
                default:
                    return 0;
            }
        });
        
        actualizarVista();
    };

    window.limpiarFiltros = function() {
        document.getElementById('search-input').value = '';
        document.getElementById('filter-naturaleza').value = '';
        document.getElementById('filter-orden').value = 'id_desc';
        filtrarBienes();
    };

    function actualizarVista() {
        // Ocultar todas las filas
        todosLosBienes.forEach(bien => bien.element.style.display = 'none');
        
        // Calcular paginación
        const inicio = (paginaActual - 1) * itemsPorPagina;
        const fin = inicio + itemsPorPagina;
        const bienesEnPagina = bienesFiltrados.slice(inicio, fin);
        
        // Mostrar filas de la página actual
        bienesEnPagina.forEach(bien => bien.element.style.display = '');
        
        // Actualizar contador de resultados
        document.getElementById('visible-count').textContent = bienesFiltrados.length;
        document.getElementById('total-count').textContent = todosLosBienes.length;
        
        // Actualizar paginación
        actualizarPaginacion();
    }

    function actualizarPaginacion() {
        const totalPaginas = Math.ceil(bienesFiltrados.length / itemsPorPagina);
        const inicio = (paginaActual - 1) * itemsPorPagina + 1;
        const fin = Math.min(inicio + itemsPorPagina - 1, bienesFiltrados.length);
        
        // Info de paginación
        const paginationInfo = document.getElementById('pagination-info');
        if (bienesFiltrados.length > 0) {
            paginationInfo.textContent = `Mostrando ${inicio} a ${fin} de ${bienesFiltrados.length} bienes`;
        } else {
            paginationInfo.textContent = 'No hay resultados';
        }
        
        // Controles de paginación
        const paginationControls = document.getElementById('pagination-controls');
        paginationControls.innerHTML = '';
        
        if (totalPaginas > 1) {
            // Botón anterior
            const btnPrev = crearBotonPaginacion('prev', paginaActual === 1);
            btnPrev.onclick = () => {
                if (paginaActual > 1) {
                    paginaActual--;
                    actualizarVista();
                }
            };
            paginationControls.appendChild(btnPrev);
            
            // Números de página
            for (let i = 1; i <= totalPaginas; i++) {
                if (i === 1 || i === totalPaginas || (i >= paginaActual - 1 && i <= paginaActual + 1)) {
                    const btnPage = crearBotonPagina(i, i === paginaActual);
                    btnPage.onclick = () => {
                        paginaActual = i;
                        actualizarVista();
                    };
                    paginationControls.appendChild(btnPage);
                } else if (i === paginaActual - 2 || i === paginaActual + 2) {
                    const dots = document.createElement('span');
                    dots.className = 'px-2 text-imss-gray';
                    dots.textContent = '...';
                    paginationControls.appendChild(dots);
                }
            }
            
            // Botón siguiente
            const btnNext = crearBotonPaginacion('next', paginaActual === totalPaginas);
            btnNext.onclick = () => {
                if (paginaActual < totalPaginas) {
                    paginaActual++;
                    actualizarVista();
                }
            };
            paginationControls.appendChild(btnNext);
        }
    }

    function crearBotonPaginacion(tipo, disabled) {
        const btn = document.createElement('button');
        const iconClass = tipo === 'prev' ? 'chevron_left' : 'chevron_right';
        btn.className = `px-3 py-1.5 rounded-lg ${disabled ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white dark:bg-gray-800 text-imss-dark dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700'} border border-imss-border dark:border-gray-700 text-sm font-medium transition`;
        btn.innerHTML = `<span class="material-symbols-outlined text-[18px]">${iconClass}</span>`;
        btn.disabled = disabled;
        return btn;
    }

    function crearBotonPagina(numero, activo) {
        const btn = document.createElement('button');
        btn.className = `px-3 py-1.5 rounded-lg ${activo ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-imss-dark dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700'} border border-imss-border dark:border-gray-700 text-sm font-medium transition`;
        btn.textContent = numero;
        return btn;
    }

    // FUNCIÓN PARA ABRIR MODAL EN MODO CREACIÓN
    window.abrirModalNuevoBien = function() {
        console.log('🆕 Abriendo modal para NUEVO bien');
        
        limpiarFormularioBien();
        
        // Cambiar título y botón para CREAR
        document.getElementById('modal-bien-title').innerHTML = '<span class="material-symbols-outlined">add</span> NUEVO REGISTRO DE BIEN';
        document.getElementById('btn-submit-bien').textContent = 'Crear Registro';
        
        toggleModal('modal-bien');
    };

    // FUNCIÓN PARA CERRAR MODAL
    window.cerrarModalBien = function() {
        limpiarFormularioBien();
        toggleModal('modal-bien');
    };

    // FUNCIÓN PARA LIMPIAR FORMULARIO
    function limpiarFormularioBien() {
        console.log('🧹 Limpiando formulario');
        
        const form = document.getElementById('form-bien');
        if (form) {
            form.reset();
        }
        
        // IMPORTANTE: Limpiar EXPLÍCITAMENTE el campo id_bien
        const idBienInput = document.getElementById('id_bien');
        if (idBienInput) {
            idBienInput.value = '';
            console.log('Campo id_bien limpiado:', idBienInput.value);
        }
        
        document.getElementById('descripcion').value = '';
        document.getElementById('naturaleza').value = 'BMNC';
        document.getElementById('marca').value = '';
        document.getElementById('modelo').value = '';
        document.getElementById('serie').value = '';
    }

    // FUNCIÓN PARA VER DETALLE
    window.verDetalleBien = function(id) {
        const row = document.querySelector(`.bien-row[data-id="${id}"]`);
        if (!row) return;
        
        const naturalezaLabels = {
            'BC': 'Bienes de Consumo',
            'BMNC': 'Bienes Muebles No Capitalizables',
            'BMC': 'Bienes Muebles Capitalizables',
            'BPS': 'Bienes de Programas Sociales'
        };
        
        const content = document.getElementById('detalle-bien-content');
        content.innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">ID del Bien</p>
                        <p class="text-lg font-bold text-imss-dark dark:text-white">#${String(id).padStart(4, '0')}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Naturaleza</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary/10 text-primary">
                            ${row.dataset.naturaleza}
                        </span>
                    </div>
                </div>
                
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Descripción</p>
                    <p class="text-base text-imss-dark dark:text-white">${row.dataset.descripcion}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Marca</p>
                        <p class="text-sm text-imss-dark dark:text-white">${row.dataset.marca || '—'}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Modelo</p>
                        <p class="text-sm text-imss-dark dark:text-white">${row.dataset.modelo || '—'}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Serie</p>
                        <p class="text-sm text-imss-dark dark:text-white">${row.dataset.serie || '—'}</p>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-imss-border dark:border-gray-800">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Tipo de Bien</p>
                    <p class="text-sm text-imss-gray dark:text-gray-400">${naturalezaLabels[row.dataset.naturaleza] || row.dataset.naturaleza}</p>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button onclick="editarBien(${id}); toggleModal('modal-detalle-bien');" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">edit</span>
                        Editar Bien
                    </button>
                    <button onclick="toggleModal('modal-detalle-bien')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-imss-dark dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition font-semibold">
                        Cerrar
                    </button>
                </div>
            </div>
        `;
        
        toggleModal('modal-detalle-bien');
    };

    // FUNCIÓN PARA EDITAR BIEN
    window.editarBien = function(id) {
        console.log('✏️ Editando bien con ID:', id);
        
        const row = document.querySelector(`.bien-row[data-id="${id}"]`);
        if (!row) {
            mostrarNotificacion('Bien no encontrado', 'error');
            return;
        }
        
        // Primero limpiar el formulario
        limpiarFormularioBien();
        
        // IMPORTANTE: Establecer el ID en el campo oculto DESPUÉS de limpiar
        const idBienInput = document.getElementById('id_bien');
        if (idBienInput) {
            idBienInput.value = id;
            console.log('ID establecido en el campo:', idBienInput.value);
        }
        
        // Llenar formulario con los datos actuales
        document.getElementById('descripcion').value = row.dataset.descripcion;
        document.getElementById('naturaleza').value = row.dataset.naturaleza;
        document.getElementById('marca').value = row.dataset.marca;
        document.getElementById('modelo').value = row.dataset.modelo;
        document.getElementById('serie').value = row.dataset.serie;
        
        console.log('Formulario llenado con datos del bien:', {
            id: id,
            descripcion: row.dataset.descripcion,
            naturaleza: row.dataset.naturaleza
        });
        
        // Cambiar título y botón para EDITAR
        document.getElementById('modal-bien-title').innerHTML = '<span class="material-symbols-outlined">edit</span> EDITAR BIEN';
        document.getElementById('btn-submit-bien').textContent = 'Actualizar Bien';
        
        toggleModal('modal-bien');
    };

    // FUNCIÓN PARA ELIMINAR BIEN
    window.eliminarBien = function(id) {
        if (!confirm('¿Está seguro de que desea eliminar este bien?\n\nEsta acción no se puede deshacer y se verificará que el bien no esté asociado a ningún movimiento.')) {
            return;
        }
        
        // Mostrar loading
        const btnEliminar = event.target.closest('button');
        const originalHTML = btnEliminar.innerHTML;
        btnEliminar.disabled = true;
        btnEliminar.innerHTML = '<span class="material-symbols-outlined text-[20px] animate-spin">refresh</span>';
        
        fetch('api/eliminar_bien.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id_bien=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion(data.message || 'Bien eliminado correctamente', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                mostrarNotificacion(data.message || 'Error al eliminar el bien', 'error');
                btnEliminar.disabled = false;
                btnEliminar.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('Error de conexión al eliminar', 'error');
            btnEliminar.disabled = false;
            btnEliminar.innerHTML = originalHTML;
        });
    };

    // FUNCIÓN PARA EXPORTAR
    window.exportarBienes = function() {
        mostrarNotificacion('Funcionalidad de exportación en desarrollo', 'error');
    };

})();