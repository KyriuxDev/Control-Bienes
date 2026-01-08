// public/assets/js/generador_documentos.js - VERSIÓN CON CAMPOS INDEPENDIENTES PARA PRÉSTAMO Y CONSTANCIA

window.updateConstanciaFields = function () {
    const checkboxes = document.querySelectorAll('input[name="tipos_movimiento[]"]:checked');
    const tiposSeleccionados = Array.from(checkboxes).map(cb => cb.value);
    
    // Fecha de devolución para Préstamo
    const fechaDevolucionContainer = document.querySelector('#fecha-devolucion-container');
    const inputFechaDevolucion = document.querySelector('#fecha_devolucion_prestamo');

    const tienePrestamo = tiposSeleccionados.includes('Prestamo');
    if (fechaDevolucionContainer) {
        if (tienePrestamo) {
            fechaDevolucionContainer.classList.remove('hidden');
        } else {
            fechaDevolucionContainer.classList.add('hidden');
        }
        if (inputFechaDevolucion) {
            inputFechaDevolucion.required = tienePrestamo;
            // Calcular días si hay fecha seleccionada
            if (tienePrestamo && inputFechaDevolucion.value) {
                calcularDiasPrestamo();
            }
        }
    }

    // Constancia de salida - Opciones globales
    const tieneConstancia = tiposSeleccionados.includes('Constancia de salida');
    document.querySelectorAll('.constancia-only').forEach(el => {
        if (tieneConstancia) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });
    
    // Actualizar requerimiento del campo de fecha de devolución de constancia
    actualizarRequerimientoFechaConstancia();
};

// Función para actualizar el requerimiento de fecha de constancia según sujeto a devolución
window.actualizarRequerimientoFechaConstancia = function() {
    const checkboxes = document.querySelectorAll('input[name="tipos_movimiento[]"]:checked');
    const tiposSeleccionados = Array.from(checkboxes).map(cb => cb.value);
    const tieneConstancia = tiposSeleccionados.includes('Constancia de salida');
    
    const sujetoDevolucionSi = document.querySelector('input[name="sujeto_devolucion_global"][value="1"]');
    const fechaDevolucionConstanciaContainer = document.querySelector('#fecha-devolucion-constancia-container');
    const inputFechaDevolucionConstancia = document.querySelector('#fecha_devolucion_constancia');
    
    if (fechaDevolucionConstanciaContainer && inputFechaDevolucionConstancia) {
        if (tieneConstancia && sujetoDevolucionSi && sujetoDevolucionSi.checked) {
            fechaDevolucionConstanciaContainer.classList.remove('hidden');
            inputFechaDevolucionConstancia.required = true;
        } else {
            fechaDevolucionConstanciaContainer.classList.add('hidden');
            inputFechaDevolucionConstancia.required = false;
            inputFechaDevolucionConstancia.value = '';
        }
    }
};

// Función para calcular días de préstamo
window.calcularDiasPrestamo = function() {
    const fechaEmision = document.querySelector('#fecha');
    const fechaDevolucion = document.querySelector('#fecha_devolucion_prestamo');
    const diasCalculadosSpan = document.querySelector('#dias-calculados');
    const diasPrestamoHidden = document.querySelector('#dias_prestamo');
    
    if (!fechaEmision || !fechaDevolucion || !diasCalculadosSpan || !diasPrestamoHidden) {
        console.error('No se encontraron todos los elementos necesarios para calcular días');
        return;
    }
    
    const fechaEmisionValue = fechaEmision.value;
    const fechaDevolucionValue = fechaDevolucion.value;
    
    if (!fechaEmisionValue || !fechaDevolucionValue) {
        diasCalculadosSpan.textContent = '';
        diasPrestamoHidden.value = '';
        return;
    }
    
    // Calcular diferencia en días
    const fecha1 = new Date(fechaEmisionValue);
    const fecha2 = new Date(fechaDevolucionValue);
    
    // Validar que la fecha de devolución sea posterior a la emisión
    if (fecha2 <= fecha1) {
        diasCalculadosSpan.textContent = '⚠️ La fecha de devolución debe ser posterior a la fecha de emisión';
        diasCalculadosSpan.classList.remove('text-blue-600', 'dark:text-blue-400');
        diasCalculadosSpan.classList.add('text-red-600', 'dark:text-red-400');
        diasPrestamoHidden.value = '';
        return;
    }
    
    const diffTime = Math.abs(fecha2 - fecha1);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    diasCalculadosSpan.textContent = `✓ ${diffDays} día${diffDays !== 1 ? 's' : ''} de préstamo`;
    diasCalculadosSpan.classList.remove('text-red-600', 'dark:text-red-400');
    diasCalculadosSpan.classList.add('text-blue-600', 'dark:text-blue-400');
    diasPrestamoHidden.value = diffDays;
};

document.addEventListener('DOMContentLoaded', function() {
    window.updateConstanciaFields();

    // Listener para cambios en tipos de movimiento
    document.querySelectorAll('input[name="tipos_movimiento[]"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', window.updateConstanciaFields);
    });
    
    // Listener para cambios en sujeto a devolución
    document.querySelectorAll('input[name="sujeto_devolucion_global"]').forEach(function(radio) {
        radio.addEventListener('change', window.actualizarRequerimientoFechaConstancia);
    });
    
    // Listener para calcular días cuando cambia la fecha de emisión
    const fechaEmision = document.querySelector('#fecha');
    if (fechaEmision) {
        fechaEmision.addEventListener('change', function() {
            calcularDiasPrestamo();
        });
    }
    
    // Listener para calcular días cuando cambia la fecha de devolución
    const fechaDevolucion = document.querySelector('#fecha_devolucion_prestamo');
    if (fechaDevolucion) {
        fechaDevolucion.addEventListener('change', function() {
            calcularDiasPrestamo();
        });
    }
    
    // Configurar fecha mínima para fecha de devolución (debe ser posterior a fecha de emisión)
    if (fechaEmision && fechaDevolucion) {
        fechaEmision.addEventListener('change', function() {
            if (this.value) {
                const fechaMin = new Date(this.value);
                fechaMin.setDate(fechaMin.getDate() + 1);
                fechaDevolucion.min = fechaMin.toISOString().split('T')[0];
                
                // Si ya hay una fecha de devolución, recalcular
                if (fechaDevolucion.value) {
                    calcularDiasPrestamo();
                }
            }
        });
    }
    
    // Configurar fecha mínima para fecha de devolución de constancia
    const fechaDevolucionConstancia = document.querySelector('#fecha_devolucion_constancia');
    if (fechaEmision && fechaDevolucionConstancia) {
        fechaEmision.addEventListener('change', function() {
            if (this.value) {
                const fechaMin = new Date(this.value);
                fechaMin.setDate(fechaMin.getDate() + 1);
                fechaDevolucionConstancia.min = fechaMin.toISOString().split('T')[0];
            }
        });
    }
});

window.vistaPrevia = function () {
    console.log('Vista previa iniciada');
    
    const form = document.querySelector('#document-form');
    
    if (!form) {
        alert('Error: No se encontró el formulario');
        console.error('Formulario #document-form no encontrado');
        return;
    }
    
    console.log('Formulario encontrado');
    
    // 1. Validar que al menos un tipo esté seleccionado
    const tiposSeleccionados = form.querySelectorAll('input[name="tipos_movimiento[]"]:checked');
    if (tiposSeleccionados.length === 0) {
        alert('Por favor seleccione al menos un tipo de documento');
        return;
    }
    
    console.log('Tipos de movimiento validados:', tiposSeleccionados.length);
    
    // 2. Validar trabajadores
    const selectRecibe = form.querySelector('select[name="matricula_recibe"]');
    const selectEntrega = form.querySelector('select[name="matricula_entrega"]');
    
    if (!selectRecibe || !selectRecibe.value) {
        alert('Por favor seleccione el trabajador que recibe');
        if (selectRecibe) selectRecibe.focus();
        return;
    }
    
    if (!selectEntrega || !selectEntrega.value) {
        alert('Por favor seleccione el trabajador que entrega');
        if (selectEntrega) selectEntrega.focus();
        return;
    }
    
    console.log('Trabajadores validados');

    // 3. Validar al menos un bien
    const primerBien = form.querySelector('select[name="bienes[0][id_bien]"]');
    if (!primerBien || !primerBien.value) {
        alert('Por favor seleccione al menos un bien en la lista');
        if (primerBien) primerBien.focus();
        return;
    }
    
    console.log('Bienes validados');

    // 4. Validar fecha de devolución si Préstamo está seleccionado
    const tienePrestamo = Array.from(tiposSeleccionados).some(function(cb) {
        return cb.value === 'Prestamo';
    });
    
    if (tienePrestamo) {
        const fechaDevolucionInput = form.querySelector('input[name="fecha_devolucion_prestamo"]');
        const diasPrestamoHidden = form.querySelector('input[name="dias_prestamo"]');
        
        if (fechaDevolucionInput && !fechaDevolucionInput.value) {
            alert('Por favor seleccione la fecha de devolución del préstamo');
            fechaDevolucionInput.focus();
            return;
        }
        
        if (diasPrestamoHidden && (!diasPrestamoHidden.value || diasPrestamoHidden.value <= 0)) {
            alert('La fecha de devolución debe ser posterior a la fecha de emisión');
            fechaDevolucionInput.focus();
            return;
        }
    }
    
    // 5. Validar fecha de devolución si Constancia de salida con sujeto a devolución está seleccionado
    const tieneConstancia = Array.from(tiposSeleccionados).some(function(cb) {
        return cb.value === 'Constancia de salida';
    });
    
    if (tieneConstancia) {
        const sujetoDevolucionSi = form.querySelector('input[name="sujeto_devolucion_global"][value="1"]');
        if (sujetoDevolucionSi && sujetoDevolucionSi.checked) {
            const fechaDevolucionConstanciaInput = form.querySelector('input[name="fecha_devolucion_constancia"]');
            if (fechaDevolucionConstanciaInput && !fechaDevolucionConstanciaInput.value) {
                alert('Por favor seleccione la fecha de devolución para la constancia de salida');
                fechaDevolucionConstanciaInput.focus();
                return;
            }
        }
    }
    
    console.log('Validaciones completadas');

    // 6. Configurar envío temporal para vista previa
    const actionOriginal = form.action;
    const targetOriginal = form.target;
    
    console.log('Configurando vista previa...');
    form.action = 'vista_previa_pdf.php';
    form.target = '_blank';

    // Enviar formulario
    try {
        form.submit();
        console.log('Formulario enviado');
        
        // Restaurar formulario después de enviar
        setTimeout(function() {
            form.action = actionOriginal;
            form.target = targetOriginal;
            console.log('Formulario restaurado');
        }, 200);
    } catch (error) {
        console.error('Error al enviar formulario:', error);
        alert('Error al generar vista previa: ' + error.message);
    }
};