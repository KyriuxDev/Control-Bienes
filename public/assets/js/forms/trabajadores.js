// assets/js/forms/trabajadores.js
document.getElementById('form-trabajador')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('api/guardar_trabajador.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            toggleModal('modal-trabajador');
            mostrarNotificacion('Trabajador guardado', 'success');
            this.reset();
        }
    });
});
