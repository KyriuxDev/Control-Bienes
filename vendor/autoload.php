<?php
// vendor/autoload.php
// Autoload para TCPDF y FPDI con rutas correctas

// TCPDF
$tcpdf_path = __DIR__ . '/tecnickcom/tcpdf.php';
if (file_exists($tcpdf_path)) {
    require_once $tcpdf_path;
} else {
    throw new Exception('TCPDF no encontrado en: ' . $tcpdf_path);
}

// FPDI - Autoload de setasign
$fpdi_autoload = __DIR__ . '/setasign/src/autoload.php';
if (file_exists($fpdi_autoload)) {
    require_once $fpdi_autoload;
} else {
    // Intentar cargar FPDI manualmente si no existe el autoload
    $fpdi_main = __DIR__ . '/setasign/src/Fpdi.php';
    if (file_exists($fpdi_main)) {
        require_once $fpdi_main;
    }
}

// Autoload de Composer (archivos de clase)
$composer_autoload = __DIR__ . '/composer/autoload_real.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
}

// Autoload para el proyecto (clases en src/)
spl_autoload_register(function($class) {
    // Namespace del proyecto: App\
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Verificar que las clases principales están disponibles
if (!class_exists('TCPDF')) {
    error_log('ERROR: TCPDF no se cargó correctamente');
}

if (!class_exists('setasign\Fpdi\Tcpdf\Fpdi')) {
    error_log('ADVERTENCIA: FPDI no se cargó correctamente, algunas funciones pueden no estar disponibles');
}