// public/assets/js/core/notificaciones.js
window.mostrarNotificacion = function (mensaje, tipo) {
    const notif = document.createElement('div');
    notif.className = `fixed top-20 right-4 z-[200] px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
        tipo === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    
    notif.innerHTML = `
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined">${tipo === 'success' ? 'check_circle' : 'error'}</span>
            <span class="font-medium">${mensaje}</span>
        </div>
    `;

    document.body.appendChild(notif);

    // Animar entrada
    setTimeout(() => {
        notif.style.transform = 'translateX(0)';
    }, 10);

    // Remover automáticamente
    setTimeout(() => {
        notif.style.transform = 'translateX(150%)';
        setTimeout(() => notif.remove(), 300);
    }, 3000);
};

// Inyectar estilos de animación una sola vez
if (!document.getElementById('notif-styles')) {
    const style = document.createElement('style');
    style.id = 'notif-styles';
    style.textContent = `
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }
    `;
    document.head.appendChild(style);
}