<?php
require_once 'vendor/autoload.php';

echo "Probando librerías...\n\n";

if (class_exists('TCPDF')) {
    echo "✓ TCPDF cargado correctamente\n";
} else {
    echo "✗ TCPDF NO encontrado\n";
}

if (class_exists('FPDI')) {
    echo "✓ FPDI cargado correctamente\n";
} else {
    echo "⚠ FPDI no disponible (opcional)\n";
}

echo "\n¡Todo listo para generar PDFs!\n";
