window.mostrarDatosTrabajador = function(select, tipo) {
    const panel = document.getElementById('panel-datos-' + tipo);
    const opt = select.options[select.selectedIndex];
    
    if (opt && opt.value) {
        panel.classList.remove('hidden');
        document.getElementById('val-mat-' + tipo).innerText = opt.dataset.mat || '';
        document.getElementById('val-cargo-' + tipo).innerText = opt.dataset.cargo || '';
        document.getElementById('val-ads-' + tipo).innerText = opt.dataset.ads || '';
        document.getElementById('val-tel-' + tipo).innerText = opt.dataset.tel || '';
    } else {
        panel.classList.add('hidden');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('form-trabajador')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('api/guardar_trabajador.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                toggleModal('modal-trabajador');
                if(typeof mostrarNotificacion === 'function') mostrarNotificacion('Trabajador guardado', 'success');
                this.reset();
                location.reload(); // Recargamos para actualizar los selects de PHP
            }
        });
    });
});