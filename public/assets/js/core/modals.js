// Fix para modals.js - Versión mejorada con manejo de eventos

(function() {
    'use strict';
    
    // Función global para toggle de modales
    window.toggleModal = function(modalID) {
        const modal = document.getElementById(modalID);
        if (!modal) {
            console.error("No se encontró el modal con ID:", modalID);
            return;
        }
        
        const isHidden = modal.classList.contains('opacity-0');
        
        if (isHidden) {
            // Mostrar modal
            modal.classList.remove('opacity-0', 'pointer-events-none');
            document.body.classList.add('modal-active');
        } else {
            // Ocultar modal
            modal.classList.add('opacity-0', 'pointer-events-none');
            document.body.classList.remove('modal-active');
        }
    };
    
    // Función específica para cerrar modal de bien
    window.cerrarModalBien = function() {
        console.log('🔴 Cerrando modal de bien');
        
        const modal = document.getElementById('modal-bien');
        if (!modal) {
            console.error('Modal de bien no encontrado');
            return;
        }
        
        // Limpiar formulario
        limpiarFormularioBien();
        
        // Cerrar modal
        modal.classList.add('opacity-0', 'pointer-events-none');
        document.body.classList.remove('modal-active');
    };
    
    // Función para limpiar formulario
    function limpiarFormularioBien() {
        console.log('🧹 Limpiando formulario de bien');
        
        const form = document.getElementById('form-bien');
        if (form) {
            form.reset();
        }
        
        // Limpiar campo ID explícitamente
        const idBienInput = document.getElementById('id_bien');
        if (idBienInput) {
            idBienInput.value = '';
        }
        
        // Limpiar campos individuales
        const descripcion = document.getElementById('descripcion');
        const naturaleza = document.getElementById('naturaleza');
        const marca = document.getElementById('marca');
        const modelo = document.getElementById('modelo');
        const serie = document.getElementById('serie');
        
        if (descripcion) descripcion.value = '';
        if (naturaleza) naturaleza.value = 'BMNC';
        if (marca) marca.value = '';
        if (modelo) modelo.value = '';
        if (serie) serie.value = '';
    }
    
    // Función para abrir modal en modo creación
    window.abrirModalNuevoBien = function() {
        console.log('🆕 Abriendo modal para NUEVO bien');
        
        // Limpiar formulario primero
        limpiarFormularioBien();
        
        // Cambiar título y botón para CREAR
        const titulo = document.getElementById('modal-bien-title');
        const boton = document.getElementById('btn-submit-bien');
        
        if (titulo) {
            titulo.innerHTML = '<span class="material-symbols-outlined">add</span> NUEVO REGISTRO DE BIEN';
        }
        if (boton) {
            boton.textContent = 'Crear Registro';
        }
        
        // Abrir modal
        toggleModal('modal-bien');
    };
    
    // Agregar event listeners al cargar el DOM
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🟢 Inicializando event listeners para modales');
        
        // Event listener para el overlay del modal de bien
        const modalBien = document.getElementById('modal-bien');
        if (modalBien) {
            const overlay = modalBien.querySelector('.modal-overlay');
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    // Solo cerrar si se hace clic directamente en el overlay
                    if (e.target === overlay) {
                        cerrarModalBien();
                    }
                });
            }
        }
        
        // Event listener para tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // Cerrar cualquier modal abierto
                const modalesAbiertos = document.querySelectorAll('.modal:not(.opacity-0)');
                modalesAbiertos.forEach(modal => {
                    if (modal.id === 'modal-bien') {
                        cerrarModalBien();
                    } else {
                        toggleModal(modal.id);
                    }
                });
            }
        });
    });
    
})();