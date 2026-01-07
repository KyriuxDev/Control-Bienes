// public/assets/js/generador_documentos.js - VERSIÓN FINAL SIN getElementById

window.updateConstanciaFields = function () {
    const checkboxes = document.querySelectorAll('input[name="tipos_movimiento[]"]:checked');
    const tiposSeleccionados = Array.from(checkboxes).map(cb => cb.value);
    
    // Días de préstamo
    const diasContainer = document.querySelector('#dias-prestamo-container');
    const inputDias = document.querySelector('#dias_prestamo');

    const tienePrestamo = tiposSeleccionados.includes('Prestamo');
    if (diasContainer) {
        if (tienePrestamo) {
            diasContainer.classList.remove('hidden');
        } else {
            diasContainer.classList.add('hidden');
        }
        if (inputDias) inputDias.required = tienePrestamo;
    }

    // Constancia de salida
    const tieneConstancia = tiposSeleccionados.includes('Constancia de salida');
    document.querySelectorAll('.constancia-only').forEach(el => {
        if (tieneConstancia) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });
};

document.addEventListener('DOMContentLoaded', function() {
    window.updateConstanciaFields();

    document.querySelectorAll('input[name="tipos_movimiento[]"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', window.updateConstanciaFields);
    });
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

    // 4. Validar días de préstamo si Préstamo está seleccionado
    const tienePrestamo = Array.from(tiposSeleccionados).some(function(cb) {
        return cb.value === 'Prestamo';
    });
    
    if (tienePrestamo) {
        const diasPrestamoInput = form.querySelector('input[name="dias_prestamo"]');
        if (diasPrestamoInput) {
            const dias = parseInt(diasPrestamoInput.value);
            if (!dias || dias <= 0) {
                alert('Por favor ingrese los días de préstamo (debe ser mayor a 0)');
                diasPrestamoInput.focus();
                return;
            }
        }
    }
    
    console.log('Días de préstamo validados');

    // 5. Configurar envío temporal para vista previa
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