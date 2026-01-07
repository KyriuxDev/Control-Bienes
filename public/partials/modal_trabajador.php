<!-- MODAL: NUEVO TRABAJADOR -->
<div id="modal-trabajador" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]">
    <div class="modal-overlay absolute w-full h-full bg-black opacity-50" onclick="toggleModal('modal-trabajador')"></div>
    <div class="bg-white dark:bg-[#1e2a1e] w-11/12 md:max-w-3xl mx-auto rounded-xl shadow-2xl z-50 overflow-y-auto max-h-[90vh]">
        <div class="p-6 border-b border-imss-border dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-primary/5 to-transparent">
            <h3 class="text-xl font-black text-primary flex items-center gap-2">
                <span class="material-symbols-outlined">person_add</span> 
                REGISTRAR TRABAJADOR
            </h3>
            <button onclick="toggleModal('modal-trabajador')" class="text-gray-400 hover:text-red-500 transition">
                <span class="material-symbols-outlined text-3xl">close</span>
            </button>
        </div>
        <form id="form-trabajador" class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Nombre Completo *</label>
                <input type="text" name="nombre" class="w-full rounded-lg border-gray-300 bg-gray-50 focus:ring-accent dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. Juan Pérez López" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Matrícula *</label>
                <input type="text" name="matricula" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="IMSS-0000" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Cargo *</label>
                <input type="text" name="cargo" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. Médico General" required>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Institución</label>
                <input type="text" name="institucion" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. IMSS">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Adscripción</label>
                <input type="text" name="adscripcion" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Ej. Urgencias">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Teléfono</label>
                <input type="tel" name="telefono" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="951 874 7412">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">Identificación</label>
                <input type="text" name="identificacion" class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="INE/IFE">
            </div>
            <div class="md:col-span-2 pt-4 border-t border-imss-border dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="toggleModal('modal-trabajador')" class="px-6 py-2 rounded-lg text-gray-500 font-bold hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2 rounded-lg bg-accent text-black font-bold hover:brightness-90 transition shadow-md">
                    Guardar Trabajador
                </button>
            </div>
        </form>
    </div>
</div>