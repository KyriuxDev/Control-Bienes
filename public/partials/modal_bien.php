<!-- Modal Bien - VERSIÓN CORREGIDA CON EVENTOS -->
<div id="modal-bien" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-[100]">
    <!-- Overlay con evento inline como respaldo -->
    <div class="modal-overlay absolute w-full h-full bg-black opacity-50" onclick="event.stopPropagation(); cerrarModalBien();"></div>
    
    <div class="bg-white dark:bg-[#1e2a1e] w-11/12 md:max-w-3xl mx-auto rounded-xl shadow-2xl z-50 overflow-y-auto max-h-[90vh]" onclick="event.stopPropagation();">
        <div class="p-6 border-b border-imss-border dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-primary/5 to-transparent">
            <h3 id="modal-bien-title" class="text-xl font-black text-primary flex items-center gap-2">
                <span class="material-symbols-outlined">inventory_2</span> 
                NUEVO REGISTRO DE BIEN
            </h3>
            <!-- Botón X con evento inline -->
            <button type="button" onclick="event.preventDefault(); event.stopPropagation(); cerrarModalBien();" class="text-gray-400 hover:text-red-500 transition">
                <span class="material-symbols-outlined text-3xl">close</span>
            </button>
        </div>
        
        <form id="form-bien" class="p-8 space-y-6">
            <!-- Campo oculto para el ID -->
            <input type="hidden" name="id_bien" id="id_bien" value="">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                        Descripción del Bien *
                    </label>
                    <input type="text" 
                           name="descripcion" 
                           id="descripcion" 
                           class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                           placeholder="Ej. Monitor HP 24 pulgadas LED" 
                           required>
                </div>
                
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                        Naturaleza *
                    </label>
                    <select name="naturaleza" 
                            id="naturaleza" 
                            class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                            required>
                        <option value="BC">Bienes de Consumo (BC)</option>
                        <option value="BMNC" selected>Bienes Muebles No Capitalizables (BMNC)</option>
                        <option value="BMC">Bienes Muebles Capitalizables (BMC)</option>
                        <option value="BPS">Bienes de Programas Sociales (BPS)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                        Marca
                    </label>
                    <input type="text" 
                           name="marca" 
                           id="marca" 
                           class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                           placeholder="Ej. HP">
                </div>
                
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                        Modelo
                    </label>
                    <input type="text" 
                           name="modelo" 
                           id="modelo" 
                           class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                           placeholder="Ej. EliteDisplay">
                </div>
                
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                        Serie
                    </label>
                    <input type="text" 
                           name="serie" 
                           id="serie" 
                           class="w-full rounded-lg border-gray-300 bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                           placeholder="Ej. SN123456">
                </div>
            </div>
            
            <div class="pt-4 border-t border-imss-border dark:border-gray-700 flex justify-end gap-3">
                <!-- Botón Cancelar con evento inline -->
                <button type="button" 
                        onclick="event.preventDefault(); cerrarModalBien();" 
                        class="px-6 py-2 rounded-lg text-gray-500 font-bold hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancelar
                </button>
                
                <!-- Botón Submit -->
                <button type="submit" 
                        id="btn-submit-bien" 
                        class="px-6 py-2 rounded-lg bg-primary text-white font-bold hover:bg-green-800 transition shadow-md">
                    Crear Registro
                </button>
            </div>
        </form>
    </div>
</div>