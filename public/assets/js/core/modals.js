// assets/js/core/modals.js
window.toggleModal = function (modalID) {
    const modal = document.getElementById(modalID);
    modal.classList.toggle('opacity-0');
    modal.classList.toggle('pointer-events-none');
    document.body.classList.toggle('modal-active');
};
