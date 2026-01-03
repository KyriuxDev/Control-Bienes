<?php
// test_all.php - Script maestro para ejecutar todos los tests
require_once __DIR__ . '/../../vendor/autoload.php';

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                                                            ║\n";
echo "║      SUITE COMPLETA DE PRUEBAS - CONTROL DE BIENES        ║\n";
echo "║                                                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "Este script ejecutará todas las pruebas de los casos de uso:\n";
echo "  1. Trabajador\n";
echo "  2. Bien\n";
echo "  3. Préstamo\n";
echo "  4. Resguardo\n";
echo "  5. Salida de Bien\n";
echo "\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Función para ejecutar un script de prueba
function ejecutarTest($archivo, $nombre) {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║  EJECUTANDO: $nombre\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
    $inicio = microtime(true);
    
    ob_start();
    include $archivo;
    $salida = ob_get_clean();
    
    $fin = microtime(true);
    $tiempo = round($fin - $inicio, 2);
    
    echo $salida;
    echo "\n";
    echo "Tiempo de ejecución: {$tiempo} segundos\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    // Pausa breve entre tests
    sleep(1);
}

// Menú interactivo
echo "Seleccione qué pruebas desea ejecutar:\n";
echo "  1. Pruebas de Trabajador\n";
echo "  2. Pruebas de Bien\n";
echo "  3. Pruebas de Préstamo\n";
echo "  4. Pruebas de Resguardo\n";
echo "  5. Pruebas de Salida de Bien\n";
echo "  6. Ejecutar TODAS las pruebas\n";
echo "  0. Salir\n\n";

echo "Ingrese su opción (0-6): ";
$opcion = trim(fgets(STDIN));

echo "\n";

$inicioTotal = microtime(true);

switch ($opcion) {
    case '1':
        if (file_exists(__DIR__ . '/../Unitarios/test_trabajador.php')) {
            ejecutarTest(__DIR__ . '/../Unitarios/test_trabajador.php', 'TRABAJADOR');
        } else {
            echo "⚠ Archivo test_trabajador.php no encontrado\n";
        }
        break;
        
    case '2':
        if (file_exists(__DIR__ . '/../Unitarios/test_bien.php')) {
            ejecutarTest(__DIR__ . '/../Unitarios/test_bien.php', 'BIEN');
        } else {
            echo "⚠ Archivo test_bien.php no encontrado\n";
        }
        break;
        
    case '3':
        if (file_exists(__DIR__ . '/../Unitarios/test_prestamo.php')) {
            ejecutarTest(__DIR__ . '/../Unitarios/test_prestamo.php', 'PRÉSTAMO');
        } else {
            echo "⚠ Archivo test_prestamo.php no encontrado\n";
        }
        break;
        
    case '4':
        if (file_exists(__DIR__ . '/../Unitarios/test_resguardo.php')) {
            ejecutarTest(__DIR__ . '/../Unitarios/test_resguardo.php', 'RESGUARDO');
        } else {
            echo "⚠ Archivo test_resguardo.php no encontrado\n";
        }
        break;
        
    case '5':
        if (file_exists(__DIR__ . '/../Unitarios/test_salida_bien.php')) {
            ejecutarTest(__DIR__ . '/../Unitarios/test_salida_bien.php', 'SALIDA DE BIEN');
        } else {
            echo "⚠ Archivo test_salida_bien.php no encontrado\n";
        }
        break;
        
    case '6':
        echo "Ejecutando TODAS las pruebas...\n\n";
        
        $tests = [
            ['archivo' => 'test_trabajador.php', 'nombre' => 'TRABAJADOR'],
            ['archivo' => 'test_bien.php', 'nombre' => 'BIEN'],
            ['archivo' => 'test_prestamo.php', 'nombre' => 'PRÉSTAMO'],
            ['archivo' => 'test_resguardo.php', 'nombre' => 'RESGUARDO'],
            ['archivo' => 'test_salida_bien.php', 'nombre' => 'SALIDA DE BIEN']
        ];
        
        foreach ($tests as $test) {
            if (file_exists(__DIR__ . '/../Unitarios/' . $test['archivo'])) {
                ejecutarTest(__DIR__ . '/../Unitarios/' . $test['archivo'], $test['nombre']);
            } else {
                echo "⚠ Archivo {$test['archivo']} no encontrado\n\n";
            }
        }
        break;
        
    case '0':
        echo "Saliendo...\n";
        exit(0);
        
    default:
        echo "✗ Opción inválida. Use 0-6.\n";
        exit(1);
}

$finTotal = microtime(true);
$tiempoTotal = round($finTotal - $inicioTotal, 2);

// Resumen final
echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    RESUMEN GENERAL                         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "  Tiempo total de ejecución: {$tiempoTotal} segundos\n";
echo "  Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "Pruebas completadas exitosamente.\n";
echo "═══════════════════════════════════════════════════════════\n\n";