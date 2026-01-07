// assets/js/generador_documentos.js
document.addEventListener('DOMContentLoaded', () => {
    updateConstanciaFields();

    document
        .querySelectorAll('input[name="tipo_movimiento"]')
        .forEach(radio =>
            radio.addEventListener('change', updateConstanciaFields)
        );
});

window.updateConstanciaFields = function () {
    const tipo = document.querySelector('input[name="tipo_movimiento"]:checked')?.value;
    if (!tipo) return;

    document.querySelectorAll('.constancia-only').forEach(el =>
        el.classList.toggle('hidden', tipo !== 'Constancia de salida')
    );
};
