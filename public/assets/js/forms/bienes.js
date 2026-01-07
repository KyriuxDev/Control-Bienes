// assets/js/forms/bienes.js
let bIdx = 1;

window.agregarFilaBien = function () {
    const bienesCatalogo = window.APP_DATA.bienesCatalogo;
    const div = document.createElement('div');

    div.className = "bien-row flex gap-3 p-4 bg-gray-50 rounded-lg border dark:bg-gray-800";

    let optionsHTML = '<option value="">-- Seleccionar Bien --</option>';
    bienesCatalogo.forEach(b => {
        const label = (b.serie || 'BIEN-' + b.id_bien) + ' - ' + b.descripcion;
        optionsHTML += `<option value="${b.id_bien}">${label}</option>`;
    });

    div.innerHTML = `
        <div class="flex-grow space-y-3">
            <select name="bienes[${bIdx}][id_bien]" required>${optionsHTML}</select>
        </div>
        <button type="button" onclick="this.closest('.bien-row').remove()">❌</button>
    `;

    document.getElementById('contenedor-bienes').appendChild(div);
    updateConstanciaFields();
    bIdx++;
};
