// public/assets/js/generador_documentos.js - VERSIÓN COMPLETA

window.updateConstanciaFields = function () {
    // Obtener todos los checkboxes marcados
    const checkboxes = document.querySelectorAll('input[name="tipos_movimiento[]"]:checked');
    const tiposSeleccionados = Array.from(checkboxes).map(cb => cb.value);
    
    const diasContainer = document.getElementById('dias-prestamo-container');
    const inputDias = document.getElementById('dias_prestamo');

    // Mostrar campo de días si Prestamo está seleccionado
    const tienePrestamo = tiposSeleccionados.includes('Prestamo');
    if (diasContainer) {
        diasContainer.classList.toggle('hidden', !tienePrestamo);
        if (inputDias) inputDias.required = tienePrestamo;
    }

    // Mostrar campos de "Sujeto a devolución" si Constancia está seleccionada
    const tieneConstancia = tiposSeleccionados.includes('Constancia de salida');
    document.querySelectorAll('.constancia-only').forEach(el => {
        el.classList.toggle('hidden', !tieneConstancia);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.updateConstanciaFields();

    document.querySelectorAll('input[name="tipos_movimiento[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', window.updateConstanciaFields);
    });
});

window.vistaPrevia = function () {
    const form = document.querySelector('form');
    
    // 1. Validar que al menos un tipo esté seleccionado
    const tiposSeleccionados = document.querySelectorAll('input[name="tipos_movimiento[]"]:checked');
    if (tiposSeleccionados.length === 0) {
        alert('Por favor seleccione al menos un tipo de documento');
        return;
    }
    
    // 2. Validar trabajadores
    const matriculaRecibe = document.getElementById('matricula_recibe')?.value;
    const matriculaEntrega = document.getElementById('matricula_entrega')?.value;
    
    if (!matriculaRecibe || !matriculaEntrega) {
        alert('Por favor seleccione ambos trabajadores (quien recibe y quien entrega)');
        return;
    }

    // 3. Validar al menos un bien
    const primerBien = document.querySelector('select[name="bienes[0][id_bien]"]')?.value;
    if (!primerBien) {
        alert('Por favor seleccione al menos un bien en la lista');
        return;
    }

    // 4. Validar días de préstamo si Préstamo está seleccionado
    const tienePrestamo = Array.from(tiposSeleccionados).some(cb => cb.value === 'Prestamo');
    if (tienePrestamo) {
        const diasPrestamo = document.getElementById('dias_prestamo')?.value;
        if (!diasPrestamo || diasPrestamo <= 0) {
            alert('Por favor ingrese los días de préstamo');
            return;
        }
    }

    // 5. Configurar envío temporal para vista previa (solo el primer formato)
    const actionOriginal = form.action;
    form.action = 'vista_previa_pdf.php';
    form.target = '_blank';

    form.submit();

    // 6. Restaurar formulario
    setTimeout(() => {
        form.action = actionOriginal;
        form.target = '';
    }, 100);
};