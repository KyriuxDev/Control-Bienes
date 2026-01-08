<!-- Sticky Footer Actions -->
<div class="fixed bottom-0 left-0 w-full bg-white dark:bg-[#1e2a1e] border-t border-imss-border dark:border-gray-700 shadow-lg z-50">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 h-20 flex items-center justify-between">
        <a href="index.php" 
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-imss-dark bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700">
                Cancelar
        </a>
        <div class="flex items-center gap-3">
            <button type="button" 
                    onclick="vistaPrevia()"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-primary bg-white border border-primary rounded-lg hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:bg-transparent dark:text-green-400 dark:border-green-400 dark:hover:bg-green-900/30">
                <span class="material-symbols-outlined text-[18px] mr-2">visibility</span>
                Vista Previa
            </button>
            <button type="submit" 
                    form="document-form"
                    class="inline-flex items-center px-6 py-2 text-sm font-bold text-white bg-primary rounded-lg shadow-sm hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transform transition hover:scale-[1.02]">
                <span class="material-symbols-outlined text-[20px] mr-2">save_as</span>
                Generar Documento PDF
            </button>
        </div>
    </div>
</div>