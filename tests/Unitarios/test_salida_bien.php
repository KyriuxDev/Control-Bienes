<?php
// test_salida_bien.php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLSalidaBienRepository;
use App\Infrastructure\Repository\MySQLSalidaDetalleRepository;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Application\UseCase\SalidaBien\CreateSalidaBienUseCase;
use App\Application\UseCase\SalidaBien\GetSalidaBienUseCase;
use App\Application\UseCase\SalidaBien\ListSalidasBienUseCase;
use App\Application\UseCase\SalidaBien\UpdateSalidaBienUseCase;
use App\Application\UseCase\Trabajador\CreateTrabajadorUseCase;
use App\Application\UseCase\Bien\CreateBienUseCase;
use App\Application\DTO\SalidaBienDTO;
use App\Application\DTO\TrabajadorDTO;
use App\Application\DTO\BienDTO;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║      PRUEBAS DE CASOS DE USO - SALIDA DE BIEN             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Obtener conexión a la base de datos
$db = Database::getInstance();
$pdo = $db->getConnection();

// Crear repositorios
$salidaBienRepository = new MySQLSalidaBienRepository($pdo);
$detalleRepository = new MySQLSalidaDetalleRepository($pdo);
$trabajadorRepository = new MySQLTrabajadorRepository($pdo);
$bienRepository = new MySQLBienRepository($pdo);

// Variables para almacenar IDs
$trabajadorId = null;
$bienIds = [];
$salidaId = null;

// ============================================
// PREPARACIÓN: CREAR TRABAJADOR Y BIENES
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PREPARACIÓN: CREAR DATOS DE PRUEBA\n";
echo "═══════════════════════════════════════════════════════════\n";

// Crear trabajador
try {
    $createTrabajador = new CreateTrabajadorUseCase($trabajadorRepository);
    $trabajadorDTO = new TrabajadorDTO([
        'nombre' => 'Carlos Ramírez López',
        'cargo' => 'Coordinador de Logística',
        'institucion' => 'IMSS',
        'adscripcion' => 'Administración de Recursos',
        'matricula' => 'MAT-' . time(),
        'identificacion' => 'CURP456789',
        'direccion' => 'Boulevard Principal #789',
        'telefono' => '5554567890'
    ]);
    
    $trabajador = $createTrabajador->execute($trabajadorDTO);
    $trabajadorId = $trabajador->id;
    echo "✓ Trabajador creado: ID {$trabajadorId} - {$trabajador->nombre}\n";
    
} catch (Exception $e) {
    echo "✗ ERROR al crear trabajador: {$e->getMessage()}\n";
    exit(1);
}

// Crear bienes
try {
    $createBien = new CreateBienUseCase($bienRepository);
    
    $bienes = [
        ['descripcion' => 'Proyector Epson PowerLite', 'naturaleza' => 'BC'],
        ['descripcion' => 'Pantalla de proyección 120"', 'naturaleza' => 'BC'],
        ['descripcion' => 'Sistema de audio portátil', 'naturaleza' => 'BC']
    ];
    
    foreach ($bienes as $bienData) {
        $bienDTO = new BienDTO([
            'identificacion' => 'BIEN-' . time() . '-' . rand(1000, 9999),
            'descripcion' => $bienData['descripcion'],
            'marca' => 'Genérica',
            'naturaleza' => $bienData['naturaleza'],
            'estado_fisico' => 'Nuevo'
        ]);
        
        $bien = $createBien->execute($bienDTO);
        $bienIds[] = $bien->id;
        echo "✓ Bien creado: ID {$bien->id} - {$bien->descripcion}\n";
        usleep(100000);
    }
    
    echo "\n";
    
} catch (Exception $e) {
    echo "✗ ERROR al crear bienes: {$e->getMessage()}\n";
    exit(1);
}

// ============================================
// PRUEBA 1: CREAR SALIDA DE BIEN
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 1: CREAR SALIDA DE BIEN CON MÚLTIPLES BIENES\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreateSalidaBienUseCase(
        $salidaBienRepository,
        $detalleRepository,
        $trabajadorRepository
    );
    
    $dto = new SalidaBienDTO([
        'folio' => 'SAL-' . date('Y') . '-' . time(),
        'trabajador_id' => $trabajadorId,
        'area_origen' => 'Almacén General',
        'destino' => 'Hospital Regional #2',
        'fecha_salida' => date('Y-m-d'),
        'fecha_devolucion_programada' => date('Y-m-d', strtotime('+7 days')),
        'sujeto_devolucion' => true,
        'lugar' => 'Auditorio Principal',
        'observaciones_estado' => 'Bienes en perfectas condiciones',
        'estado' => 'AUTORIZADO'
    ]);
    
    // Preparar bienes para la salida
    $bienesParaSalida = [
        ['bien_id' => $bienIds[0], 'cantidad' => 1],
        ['bien_id' => $bienIds[1], 'cantidad' => 1],
        ['bien_id' => $bienIds[2], 'cantidad' => 1]
    ];
    
    $result = $createUseCase->execute($dto, $bienesParaSalida);
    $salidaId = $result->id;
    
    echo "✓ ÉXITO: Salida de bien creada correctamente\n";
    echo "  └─ ID: {$result->id}\n";
    echo "  └─ Folio: {$result->folio}\n";
    echo "  └─ Trabajador ID: {$result->trabajador_id}\n";
    echo "  └─ Área origen: {$result->area_origen}\n";
    echo "  └─ Destino: {$result->destino}\n";
    echo "  └─ Fecha salida: {$result->fecha_salida}\n";
    echo "  └─ Fecha devolución programada: {$result->fecha_devolucion_programada}\n";
    echo "  └─ Sujeto a devolución: " . ($result->sujeto_devolucion ? 'Sí' : 'No') . "\n";
    echo "  └─ Estado: {$result->estado}\n";
    echo "  └─ Bienes incluidos: " . count($bienesParaSalida) . "\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 2: VALIDAR SALIDA SIN BIENES
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 2: VALIDAR SALIDA SIN BIENES (debe fallar)\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreateSalidaBienUseCase(
        $salidaBienRepository,
        $detalleRepository,
        $trabajadorRepository
    );
    
    $dto = new SalidaBienDTO([
        'folio' => 'SAL-INVALIDO',
        'trabajador_id' => $trabajadorId,
        'destino' => 'Destino Test',
        'fecha_salida' => date('Y-m-d')
    ]);
    
    $result = $createUseCase->execute($dto, []); // Array vacío de bienes
    echo "✗ FALLO: Debería haber lanzado una excepción\n\n";
    
} catch (Exception $e) {
    echo "✓ ÉXITO: Validación correcta - {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 3: VALIDAR SALIDA SIN DESTINO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 3: VALIDAR SALIDA SIN DESTINO (debe fallar)\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreateSalidaBienUseCase(
        $salidaBienRepository,
        $detalleRepository,
        $trabajadorRepository
    );
    
    $dto = new SalidaBienDTO([
        'folio' => 'SAL-INVALIDO-2',
        'trabajador_id' => $trabajadorId,
        'fecha_salida' => date('Y-m-d')
    ]);
    
    $bienesTest = [['bien_id' => $bienIds[0], 'cantidad' => 1]];
    $result = $createUseCase->execute($dto, $bienesTest);
    echo "✗ FALLO: Debería haber lanzado una excepción\n\n";
    
} catch (Exception $e) {
    echo "✓ ÉXITO: Validación correcta - {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 4: OBTENER SALIDA POR ID
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 4: OBTENER SALIDA DE BIEN POR ID\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($salidaId) {
    try {
        $getUseCase = new GetSalidaBienUseCase($salidaBienRepository, $detalleRepository);
        $salida = $getUseCase->execute($salidaId);
        
        echo "✓ ÉXITO: Salida encontrada\n";
        echo "  └─ ID: {$salida->id}\n";
        echo "  └─ Folio: {$salida->folio}\n";
        echo "  └─ Destino: {$salida->destino}\n";
        echo "  └─ Estado: {$salida->estado}\n";
        echo "  └─ Observaciones: {$salida->observaciones_estado}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 5: OBTENER SALIDA CON DETALLES
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 5: OBTENER SALIDA CON DETALLES DE BIENES\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($salidaId) {
    try {
        $getUseCase = new GetSalidaBienUseCase($salidaBienRepository, $detalleRepository);
        $resultado = $getUseCase->executeWithDetails($salidaId);
        
        echo "✓ ÉXITO: Salida con detalles obtenida\n";
        echo "  └─ Folio: {$resultado['salida_bien']->folio}\n";
        echo "  └─ Número de bienes: " . count($resultado['detalles']) . "\n";
        echo "  └─ Detalles:\n";
        
        foreach ($resultado['detalles'] as $detalle) {
            echo "     • Bien ID: {$detalle->getBienId()} - Cantidad: {$detalle->getCantidad()}\n";
        }
        echo "\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 6: LISTAR TODAS LAS SALIDAS
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 6: LISTAR TODAS LAS SALIDAS DE BIEN\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $listUseCase = new ListSalidasBienUseCase($salidaBienRepository);
    $salidas = $listUseCase->execute();
    
    echo "✓ ÉXITO: Salidas listadas\n";
    echo "  └─ Total de salidas: " . count($salidas) . "\n\n";
    
    if (count($salidas) > 0) {
        echo "  Últimas 3 salidas:\n";
        foreach (array_slice($salidas, -3) as $s) {
            echo "  • Folio: {$s->folio} | Estado: {$s->estado} | Destino: {$s->destino}\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 7: LISTAR SALIDAS POR ESTADO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 7: LISTAR SALIDAS AUTORIZADAS\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $listUseCase = new ListSalidasBienUseCase($salidaBienRepository);
    $salidas = $listUseCase->executeByEstado('AUTORIZADO');
    
    echo "✓ ÉXITO: Salidas autorizadas listadas\n";
    echo "  └─ Total de salidas autorizadas: " . count($salidas) . "\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 8: LISTAR SALIDAS POR TRABAJADOR
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 8: LISTAR SALIDAS DEL TRABAJADOR\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($trabajadorId) {
    try {
        $listUseCase = new ListSalidasBienUseCase($salidaBienRepository);
        $salidas = $listUseCase->executeByTrabajador($trabajadorId);
        
        echo "✓ ÉXITO: Salidas del trabajador listadas\n";
        echo "  └─ Trabajador ID: {$trabajadorId}\n";
        echo "  └─ Total de salidas: " . count($salidas) . "\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 9: LISTAR SALIDAS SUJETAS A DEVOLUCIÓN
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 9: LISTAR SALIDAS SUJETAS A DEVOLUCIÓN\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $listUseCase = new ListSalidasBienUseCase($salidaBienRepository);
    $salidas = $listUseCase->executeSujetasDevolucion();
    
    echo "✓ ÉXITO: Salidas sujetas a devolución listadas\n";
    echo "  └─ Total: " . count($salidas) . "\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 10: ACTUALIZAR SALIDA
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 10: ACTUALIZAR SALIDA DE BIEN\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($salidaId) {
    try {
        $updateUseCase = new UpdateSalidaBienUseCase($salidaBienRepository);
        
        $dto = new SalidaBienDTO([
            'id' => $salidaId,
            'estado' => 'EN_TRANSITO',
            'observaciones_estado' => 'Bienes en tránsito hacia el destino',
            'fecha_devolucion_programada' => date('Y-m-d', strtotime('+10 days'))
        ]);
        
        $result = $updateUseCase->execute($dto);
        
        echo "✓ ÉXITO: Salida actualizada\n";
        echo "  └─ ID: {$result->id}\n";
        echo "  └─ Nuevo estado: {$result->estado}\n";
        echo "  └─ Nuevas observaciones: {$result->observaciones_estado}\n";
        echo "  └─ Nueva fecha devolución: {$result->fecha_devolucion_programada}\n\n";
        
        // Verificar actualización
        $getUseCase = new GetSalidaBienUseCase($salidaBienRepository, $detalleRepository);
        $salidaActualizada = $getUseCase->execute($salidaId);
        echo "  Verificación:\n";
        echo "  └─ Estado en BD: {$salidaActualizada->estado}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 11: LISTAR SALIDAS EN TRÁNSITO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 11: LISTAR SALIDAS EN TRÁNSITO\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $listUseCase = new ListSalidasBienUseCase($salidaBienRepository);
    $salidas = $listUseCase->executeEnTransito();
    
    echo "✓ ÉXITO: Salidas en tránsito listadas\n";
    echo "  └─ Total en tránsito: " . count($salidas) . "\n";
    
    if (count($salidas) > 0) {
        echo "  └─ Salidas:\n";
        foreach ($salidas as $s) {
            echo "     • Folio: {$s->folio} | Destino: {$s->destino}\n";
        }
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 12: CREAR SALIDA SIN DEVOLUCIÓN
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 12: CREAR SALIDA NO SUJETA A DEVOLUCIÓN\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreateSalidaBienUseCase(
        $salidaBienRepository,
        $detalleRepository,
        $trabajadorRepository
    );
    
    $dto = new SalidaBienDTO([
        'folio' => 'SAL-PERM-' . time(),
        'trabajador_id' => $trabajadorId,
        'area_origen' => 'Almacén',
        'destino' => 'Clínica Regional #5',
        'fecha_salida' => date('Y-m-d'),
        'sujeto_devolucion' => false, // NO sujeto a devolución
        'lugar' => 'Instalación permanente',
        'observaciones_estado' => 'Traslado definitivo',
        'estado' => 'AUTORIZADO'
    ]);
    
    $bienesTest = [['bien_id' => $bienIds[0], 'cantidad' => 1]];
    $result = $createUseCase->execute($dto, $bienesTest);
    
    echo "✓ ÉXITO: Salida sin devolución creada\n";
    echo "  └─ ID: {$result->id}\n";
    echo "  └─ Folio: {$result->folio}\n";
    echo "  └─ Sujeto a devolución: " . ($result->sujeto_devolucion ? 'Sí' : 'No') . "\n";
    echo "  └─ Destino: {$result->destino}\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// RESUMEN FINAL
// ============================================
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                  PRUEBAS COMPLETADAS                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";