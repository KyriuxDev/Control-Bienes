// public/assets/js/gestion_documentos.js

(function() {
    'use strict';

    // Variables globales
    let todosLosDocumentos = [];
    let documentosFiltrados = [];
    let paginaActual = 1;
    const itemsPorPagina = 10;

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        cargarDocumentos();
    });

    function cargarDocumentos() {
        const rows = document.querySelectorAll('.documento-row');
        todosLosDocumentos = Array.from(rows).map(row => ({
            id: row.dataset.id,
            folio: row.dataset.folio.toLowerCase(),
            tipo: row.dataset.tipo,
            responsable: row.dataset.responsable.toLowerCase(),
            fecha: row.dataset.fecha,
            area: row.dataset.area.toLowerCase(),
            element: row
        }));
        documentosFiltrados = [...todosLosDocumentos];
        actualizarVista();
    }

    // Funciones de filtrado
    window.filtrarDocumentos = function() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const tipoFilter = document.getElementById('filter-tipo').value;
        
        documentosFiltrados = todosLosDocumentos.filter(doc => {
            const matchSearch = !searchTerm || 
                doc.folio.includes(searchTerm) ||
                doc.responsable.includes(searchTerm) ||
                doc.area.includes(searchTerm);
            
            const matchTipo = !tipoFilter || doc.tipo === tipoFilter;
            
            return matchSearch && matchTipo;
        });
        
        paginaActual = 1;
        actualizarVista();
    };

    window.limpiarFiltros = function() {
        document.getElementById('search-input').value = '';
        document.getElementById('filter-tipo').value = '';
        filtrarDocumentos();
    };

    function actualizarVista() {
        // Ocultar todas las filas
        todosLosDocumentos.forEach(doc => doc.element.style.display = 'none');
        
        // Calcular paginación
        const inicio = (paginaActual - 1) * itemsPorPagina;
        const fin = inicio + itemsPorPagina;
        const documentosEnPagina = documentosFiltrados.slice(inicio, fin);
        
        // Mostrar filas de la página actual
        documentosEnPagina.forEach(doc => doc.element.style.display = '');
        
        // Actualizar contador de resultados
        document.getElementById('visible-count').textContent = documentosFiltrados.length;
        document.getElementById('total-count').textContent = todosLosDocumentos.length;
        
        // Actualizar paginación
        actualizarPaginacion();
    }

    function actualizarPaginacion() {
        const totalPaginas = Math.ceil(documentosFiltrados.length / itemsPorPagina);
        const inicio = (paginaActual - 1) * itemsPorPagina + 1;
        const fin = Math.min(inicio + itemsPorPagina - 1, documentosFiltrados.length);
        
        // Info de paginación
        const paginationInfo = document.getElementById('pagination-info');
        if (documentosFiltrados.length > 0) {
            paginationInfo.textContent = `Mostrando ${inicio} a ${fin} de ${documentosFiltrados.length} documentos`;
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

    // FUNCIÓN PARA VER DETALLE DEL DOCUMENTO
    window.verDetalleDocumento = function(id) {
        console.log('Mostrando detalle del documento:', id);
        
        // Mostrar loading
        const content = document.getElementById('detalle-documento-content');
        content.innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>
        `;
        
        toggleModal('modal-detalle-documento');
        
        // Hacer petición AJAX para obtener los detalles
        fetch(`api/obtener_detalle_documento.php?id=${id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    mostrarDetalleDocumento(data.movimiento, data.detalles, data.trabajadores);
                } else {
                    content.innerHTML = `
                        <div class="text-center py-8">
                            <span class="material-symbols-outlined text-red-500 text-5xl">error</span>
                            <p class="mt-4 text-red-600">${data.message || 'Error al cargar los detalles'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `
                    <div class="text-center py-8">
                        <span class="material-symbols-outlined text-red-500 text-5xl">error</span>
                        <p class="mt-4 text-red-600">Error de conexión</p>
                    </div>
                `;
            });
    };

    function mostrarDetalleDocumento(movimiento, detalles, trabajadores) {
        const tipoLabels = {
            'Resguardo': { color: 'green', icon: 'shield_person' },
            'Prestamo': { color: 'blue', icon: 'swap_horiz' },
            'Constancia de salida': { color: 'orange', icon: 'logout' }
        };
        
        const tipoInfo = tipoLabels[movimiento.tipo_movimiento] || { color: 'gray', icon: 'description' };
        
        let html = `
            <div class="space-y-6">
                <!-- Información General -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Folio</p>
                        <p class="text-lg font-bold text-imss-dark dark:text-white">${movimiento.folio}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Tipo de Documento</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-${tipoInfo.color}-100 text-${tipoInfo.color}-800 dark:bg-${tipoInfo.color}-900/40 dark:text-${tipoInfo.color}-200">
                            <span class="material-symbols-outlined text-sm mr-1">${tipoInfo.icon}</span>
                            ${movimiento.tipo_movimiento}
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Fecha</p>
                        <p class="text-sm text-imss-dark dark:text-white">${formatearFecha(movimiento.fecha)}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Lugar</p>
                        <p class="text-sm text-imss-dark dark:text-white">${movimiento.lugar || '—'}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Área</p>
                        <p class="text-sm text-imss-dark dark:text-white">${movimiento.area || '—'}</p>
                    </div>
                    ${movimiento.dias_prestamo ? `
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Días de Préstamo</p>
                        <p class="text-sm text-imss-dark dark:text-white">${movimiento.dias_prestamo} días</p>
                    </div>
                    ` : ''}
                </div>
                
                <!-- Responsables -->
                <div class="pt-4 border-t border-imss-border dark:border-gray-800">
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">Responsables</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-xs font-bold text-blue-600 dark:text-blue-400 mb-2">RECIBE</p>
                            <p class="font-bold text-imss-dark dark:text-white">${trabajadores.recibe.nombre}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">${trabajadores.recibe.cargo}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Mat: ${trabajadores.recibe.matricula}</p>
                        </div>
                        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <p class="text-xs font-bold text-green-600 dark:text-green-400 mb-2">ENTREGA</p>
                            <p class="font-bold text-imss-dark dark:text-white">${trabajadores.entrega.nombre}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">${trabajadores.entrega.cargo}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Mat: ${trabajadores.entrega.matricula}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Bienes -->
                <div class="pt-4 border-t border-imss-border dark:border-gray-800">
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">Bienes Asociados (${detalles.length})</p>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        ${detalles.map(detalle => `
                            <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10 text-primary flex-shrink-0">
                                    <span class="material-symbols-outlined">inventory</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm text-imss-dark dark:text-white truncate">${detalle.bien.descripcion}</p>
                                    <div class="flex items-center gap-4 mt-1 text-xs text-gray-600 dark:text-gray-400">
                                        <span>Cantidad: <strong>${detalle.cantidad}</strong></span>
                                        ${detalle.bien.marca ? `<span>Marca: ${detalle.bien.marca}</span>` : ''}
                                        ${detalle.bien.serie ? `<span>Serie: ${detalle.bien.serie}</span>` : ''}
                                    </div>
                                    ${detalle.estado_fisico ? `<p class="text-xs text-gray-500 mt-1">Estado: ${detalle.estado_fisico}</p>` : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button onclick="descargarDocumento(${movimiento.id_movimiento})" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">download</span>
                        Descargar PDF
                    </button>
                    <button onclick="toggleModal('modal-detalle-documento')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-imss-dark dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition font-semibold">
                        Cerrar
                    </button>
                </div>
            </div>
        `;
        
        document.getElementById('detalle-documento-content').innerHTML = html;
    }

    function formatearFecha(fecha) {
        const date = new Date(fecha);
        const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('es-MX', opciones);
    }

    // FUNCIÓN PARA DESCARGAR DOCUMENTO
    window.descargarDocumento = function(id) {
        console.log('Descargando documento:', id);
        
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion('Funcionalidad en desarrollo', 'error');
        } else {
            alert('Funcionalidad en desarrollo');
        }
    };

    // FUNCIÓN PARA ELIMINAR DOCUMENTO
    window.eliminarDocumento = function(id) {
        if (!confirm('¿Está seguro de que desea eliminar este documento?\n\nEsta acción eliminará el movimiento y todos sus detalles asociados.')) {
            return;
        }
        
        const btnEliminar = event.target.closest('button');
        const originalHTML = btnEliminar.innerHTML;
        btnEliminar.disabled = true;
        btnEliminar.innerHTML = '<span class="material-symbols-outlined text-[20px] animate-spin">refresh</span>';
        
        fetch('api/eliminar_documento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id_movimiento=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof mostrarNotificacion === 'function') {
                    mostrarNotificacion(data.message || 'Documento eliminado correctamente', 'success');
                }
                setTimeout(() => location.reload(), 1000);
            } else {
                if (typeof mostrarNotificacion === 'function') {
                    mostrarNotificacion(data.message || 'Error al eliminar el documento', 'error');
                } else {
                    alert(data.message || 'Error al eliminar el documento');
                }
                btnEliminar.disabled = false;
                btnEliminar.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion('Error de conexión al eliminar', 'error');
            } else {
                alert('Error de conexión al eliminar');
            }
            btnEliminar.disabled = false;
            btnEliminar.innerHTML = originalHTML;
        });
    };

    // FUNCIÓN PARA EXPORTAR
    window.exportarDocumentos = function() {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion('Funcionalidad de exportación en desarrollo', 'error');
        } else {
            alert('Funcionalidad de exportación en desarrollo');
        }
    };

})();