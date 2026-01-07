// public/assets/js/generador_documentos.js - VERSIÓN COMPLETA CON VALIDACIÓN DE FOLIO

let folioValidado = false;
let timeoutFolio = null;

window.updateConstanciaFields = function () {
    const checkboxes = document.querySelectorAll('input[name="tipos_movimiento[]"]:checked');
    const tiposSeleccionados = Array.from(checkboxes).map(cb => cb.value);
    
    const diasContainer = document.getElementById('dias-prestamo-container');
    const inputDias = document.getElementById('dias_prestamo');

    const tienePrestamo = tiposSeleccionados.includes('Prestamo');
    if (diasContainer) {
        diasContainer.classList.toggle('hidden', !tienePrestamo);
        if (inputDias) inputDias.required = tienePrestamo;
    }

    const tieneConstancia = tiposSeleccionados.includes('Constancia de salida');
    document.querySelectorAll('.constancia-only').forEach(el => {
        el.classList.toggle('hidden', !tieneConstancia);
    });
};

// Validar folio en tiempo real
function validarFolio() {
    const inputFolio = document.getElementById('folio');
    const folio = inputFolio.value.trim();
    
    // Limpiar clases previas
    inputFolio.classList.remove('border-green-500', 'border-red-500', 'bg-green-50', 'bg-red-50');
    
    // Remover mensaje anterior
    const mensajeAnterior = inputFolio.parentElement.querySelector('.mensaje-validacion');
    if (mensajeAnterior) mensajeAnterior.remove();
    
    if (folio === '') {
        folioValidado = false;
        return;
    }
    
    // Cancelar timeout anterior
    if (timeoutFolio) clearTimeout(timeoutFolio);
    
    // Esperar 500ms después de que el usuario deje de escribir
    timeoutFolio = setTimeout(() => {
        fetch('api/validar_folio.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'folio=' + encodeURIComponent(folio)
        })
        .then(r => r.json())
        .then(data => {
            if (data.existe) {
                folioValidado = false;
                inputFolio.classList.add('border-red-500', 'bg-red-50', 'dark:bg-red-900/20');
                
                const mensaje = document.createElement('p');
                mensaje.className = 'mensaje-validacion mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1';
                mensaje.innerHTML = '<span class="material-symbols-outlined text-sm">error</span> Este folio ya existe en el sistema';
                inputFolio.parentElement.appendChild(mensaje);
            } else {
                folioValidado = true;
                inputFolio.classList.add('border-green-500', 'bg-green-50', 'dark:bg-green-900/20');
                
                const mensaje = document.createElement('p');
                mensaje.className = 'mensaje-validacion mt-1 text-xs text-green-600 dark:text-green-400 flex items-center gap-1';
                mensaje.innerHTML = '<span class="material-symbols-outlined text-sm">check_circle</span> Folio disponible';
                inputFolio.parentElement.appendChild(mensaje);
            }
        })
        .catch(err => {
            console.error('Error al validar folio:', err);
            folioValidado = false;
        });
    }, 500);
}

document.addEventListener('DOMContentLoaded', () => {
    window.updateConstanciaFields();

    document.querySelectorAll('input[name="tipos_movimiento[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', window.updateConstanciaFields);
    });
    
    // Agregar validación de folio
    const inputFolio = document.getElementById('folio');
    if (inputFolio) {
        inputFolio.addEventListener('input', validarFolio);
        inputFolio.addEventListener('blur', validarFolio);
    }
});

window.vistaPrevia = function () {
    const form = document.querySelector('form');
    
    // 1. Validar folio
    const folio = document.getElementById('folio').value.trim();
    if (!folio) {
        alert('Por favor ingrese el folio del documento');
        document.getElementById('folio').focus();
        return;
    }
    
    if (!folioValidado) {
        alert('Por favor espere a que se valide el folio o ingrese uno válido');
        document.getElementById('folio').focus();
        return;
    }
    
    // 2. Validar que al menos un tipo esté seleccionado
    const tiposSeleccionados = document.querySelectorAll('input[name="tipos_movimiento[]"]:checked');
    if (tiposSeleccionados.length === 0) {
        alert('Por favor seleccione al menos un tipo de documento');
        return;
    }
    
    // 3. Validar trabajadores
    const matriculaRecibe = document.getElementById('matricula_recibe')?.value;
    const matriculaEntrega = document.getElementById('matricula_entrega')?.value;
    
    if (!matriculaRecibe || !matriculaEntrega) {
        alert('Por favor seleccione ambos trabajadores (quien recibe y quien entrega)');
        return;
    }

    // 4. Validar al menos un bien
    const primerBien = document.querySelector('select[name="bienes[0][id_bien]"]')?.value;
    if (!primerBien) {
        alert('Por favor seleccione al menos un bien en la lista');
        return;
    }

    // 5. Validar días de préstamo si Préstamo está seleccionado
    const tienePrestamo = Array.from(tiposSeleccionados).some(cb => cb.value === 'Prestamo');
    if (tienePrestamo) {
        const diasPrestamo = document.getElementById('dias_prestamo')?.value;
        if (!diasPrestamo || diasPrestamo <= 0) {
            alert('Por favor ingrese los días de préstamo');
            return;
        }
    }

    // 6. Configurar envío temporal para vista previa
    const actionOriginal = form.action;
    form.action = 'vista_previa_pdf.php';
    form.target = '_blank';

    form.submit();

    // 7. Restaurar formulario
    setTimeout(() => {
        form.action = actionOriginal;
        form.target = '';
    }, 100);
};