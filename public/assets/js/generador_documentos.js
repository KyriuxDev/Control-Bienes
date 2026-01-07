window.updateConstanciaFields = function () {
    const radioChecked = document.querySelector('input[name="tipo_movimiento"]:checked');
    if (!radioChecked) return;
    
    const tipo = radioChecked.value;
    const diasContainer = document.getElementById('dias-prestamo-container');

    // Campos de "Sujeto a devolución" en las filas de bienes
    document.querySelectorAll('.constancia-only').forEach(el => {
        el.classList.toggle('hidden', tipo !== 'Constancia de salida');
    });

    // Campo de "Días de préstamo"
    if (diasContainer) {
        diasContainer.classList.toggle('hidden', tipo !== 'Prestamo');
        const inputDias = document.getElementById('dias_prestamo');
        if (inputDias) inputDias.required = (tipo === 'Prestamo');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    window.updateConstanciaFields();

    document.querySelectorAll('input[name="tipo_movimiento"]').forEach(radio => {
        radio.addEventListener('change', window.updateConstanciaFields);
    });
});

// Añadir al final de public/assets/js/generador_documentos.js

window.vistaPrevia = function () {
    const form = document.querySelector('form');
    
    // 1. Validar trabajadores
    const matriculaRecibe = document.getElementById('matricula_recibe')?.value;
    const matriculaEntrega = document.getElementById('matricula_entrega')?.value;
    
    if (!matriculaRecibe || !matriculaEntrega) {
        alert('Por favor seleccione ambos trabajadores (quien recibe y quien entrega)');
        return;
    }

    // 2. Validar al menos un bien
    const primerBien = document.querySelector('select[name="bienes[0][id_bien]"]')?.value;
    if (!primerBien) {
        alert('Por favor seleccione al menos un bien en la lista');
        return;
    }

    // 3. Configurar envío temporal para vista previa
    const actionOriginal = form.action;
    form.action = 'vista_previa_pdf.php';
    form.target = '_blank'; // Abrir en pestaña nueva

    form.submit();

    // 4. Restaurar formulario para el envío real
    setTimeout(() => {
        form.action = actionOriginal;
        form.target = '';
    }, 100);
};