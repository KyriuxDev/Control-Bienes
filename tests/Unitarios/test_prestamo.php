<?php
// test_prestamo.php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLPrestamoRepository;
use App\Infrastructure\Repository\MySQLPrestamoDetalleRepository;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Application\UseCase\Prestamo\CreatePrestamoUseCase;
use App\Application\UseCase\Prestamo\GetPrestamoUseCase;
use App\Application\UseCase\Prestamo\ListPrestamosUseCase;
use App\Application\UseCase\Prestamo\UpdatePrestamoUseCase;
use App\Application\UseCase\Prestamo\DevolverPrestamoUseCase;
use App\Application\UseCase\Trabajador\CreateTrabajadorUseCase;
use App\Application\UseCase\Bien\CreateBienUseCase;
use App\Application\DTO\PrestamoDTO;
use App\Application\DTO\TrabajadorDTO;
use App\Application\DTO\BienDTO;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║        PRUEBAS DE CASOS DE USO - PRÉSTAMO                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Obtener conexión a la base de datos
$db = Database::getInstance();
$pdo = $db->getConnection();

// Crear repositorios
$prestamoRepository = new MySQLPrestamoRepository($pdo);
$detalleRepository = new MySQLPrestamoDetalleRepository($pdo);
$trabajadorRepository = new MySQLTrabajadorRepository($pdo);
$bienRepository = new MySQLBienRepository($pdo);

// Variables para almacenar IDs
$trabajadorId = null;
$bienIds = [];
$prestamoId = null;

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
        'nombre' => 'Juan Pérez García',
        'cargo' => 'Jefe de Departamento',
        'institucion' => 'IMSS',
        'adscripcion' => 'Administración',
        'matricula' => 'MAT-' . time(),
        'identificacion' => 'CURP123456',
        'direccion' => 'Av. Principal #123',
        'telefono' => '5551234567'
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
        ['descripcion' => 'Laptop Dell Latitude', 'naturaleza' => 'BC'],
        ['descripcion' => 'Mouse inalámbrico Logitech', 'naturaleza' => 'BC'],
        ['descripcion' => 'Monitor Samsung 24"', 'naturaleza' => 'BC']
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
// PRUEBA 1: CREAR PRÉSTAMO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 1: CREAR PRÉSTAMO CON MÚLTIPLES BIENES\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreatePrestamoUseCase(
        $prestamoRepository,
        $detalleRepository,
        $trabajadorRepository
    );
    
    $dto = new PrestamoDTO([
        'folio' => 'PRE-' . date('Y') . '-' . time(),
        'trabajador_id' => $trabajadorId,
        'fecha_emision' => date('Y-m-d'),
        'fecha_devolucion_programada' => date('Y-m-d', strtotime('+30 days')),
        'lugar' => 'Oficina Central',
        'matricula_autoriza' => 'MAT-AUTO-001',
        'matricula_recibe' => 'MAT-' . time(),
        'estado' => 'ACTIVO',
        'observaciones' => 'Préstamo para trabajo remoto'
    ]);
    
    // Preparar bienes para el préstamo
    $bienesParaPrestamo = [
        ['bien_id' => $bienIds[0], 'cantidad' => 1],
        ['bien_id' => $bienIds[1], 'cantidad' => 1],
        ['bien_id' => $bienIds[2], 'cantidad' => 1]
    ];
    
    $result = $createUseCase->execute($dto, $bienesParaPrestamo);
    $prestamoId = $result->id;
    
    echo "✓ ÉXITO: Préstamo creado correctamente\n";
    echo "  └─ ID: {$result->id}\n";
    echo "  └─ Folio: {$result->folio}\n";
    echo "  └─ Trabajador ID: {$result->trabajador_id}\n";
    echo "  └─ Fecha emisión: {$result->fecha_emision}\n";
    echo "  └─ Fecha devolución programada: {$result->fecha_devolucion_programada}\n";
    echo "  └─ Estado: {$result->estado}\n";
    echo "  └─ Lugar: {$result->lugar}\n";
    echo "  └─ Bienes incluidos: " . count($bienesParaPrestamo) . "\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 2: VALIDAR PRÉSTAMO SIN BIENES
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 2: VALIDAR PRÉSTAMO SIN BIENES (debe fallar)\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreatePrestamoUseCase(
        $prestamoRepository,
        $detalleRepository,
        $trabajadorRepository
    );
    
    $dto = new PrestamoDTO([
        'folio' => 'PRE-INVALIDO',
        'trabajador_id' => $trabajadorId,
        'fecha_emision' => date('Y-m-d'),
        'fecha_devolucion_programada' => date('Y-m-d', strtotime('+30 days'))
    ]);
    
    $result = $createUseCase->execute($dto, []); // Array vacío de bienes
    echo "✗ FALLO: Debería haber lanzado una excepción\n\n";
    
} catch (Exception $e) {
    echo "✓ ÉXITO: Validación correcta - {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 3: OBTENER PRÉSTAMO POR ID
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 3: OBTENER PRÉSTAMO POR ID\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($prestamoId) {
    try {
        $getUseCase = new GetPrestamoUseCase($prestamoRepository, $detalleRepository);
        $prestamo = $getUseCase->execute($prestamoId);
        
        echo "✓ ÉXITO: Préstamo encontrado\n";
        echo "  └─ ID: {$prestamo->id}\n";
        echo "  └─ Folio: {$prestamo->folio}\n";
        echo "  └─ Trabajador: {$prestamo->trabajador_id}\n";
        echo "  └─ Estado: {$prestamo->estado}\n";
        echo "  └─ Observaciones: {$prestamo->observaciones}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 4: OBTENER PRÉSTAMO CON DETALLES
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 4: OBTENER PRÉSTAMO CON DETALLES DE BIENES\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($prestamoId) {
    try {
        $getUseCase = new GetPrestamoUseCase($prestamoRepository, $detalleRepository);
        $resultado = $getUseCase->executeWithDetails($prestamoId);
        
        echo "✓ ÉXITO: Préstamo con detalles obtenido\n";
        echo "  └─ Folio: {$resultado['prestamo']->folio}\n";
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
// PRUEBA 5: LISTAR PRÉSTAMOS
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 5: LISTAR TODOS LOS PRÉSTAMOS\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $listUseCase = new ListPrestamosUseCase($prestamoRepository);
    $prestamos = $listUseCase->execute();
    
    echo "✓ ÉXITO: Préstamos listados\n";
    echo "  └─ Total de préstamos: " . count($prestamos) . "\n\n";
    
    if (count($prestamos) > 0) {
        echo "  Últimos 3 préstamos:\n";
        foreach (array_slice($prestamos, -3) as $p) {
            echo "  • Folio: {$p->folio} | Estado: {$p->estado} | Fecha emisión: {$p->fecha_emision}\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 6: LISTAR PRÉSTAMOS POR ESTADO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 6: LISTAR PRÉSTAMOS ACTIVOS\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $listUseCase = new ListPrestamosUseCase($prestamoRepository);
    $prestamos = $listUseCase->executeByEstado('ACTIVO');
    
    echo "✓ ÉXITO: Préstamos activos listados\n";
    echo "  └─ Total de préstamos activos: " . count($prestamos) . "\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 7: LISTAR PRÉSTAMOS POR TRABAJADOR
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 7: LISTAR PRÉSTAMOS DEL TRABAJADOR\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($trabajadorId) {
    try {
        $listUseCase = new ListPrestamosUseCase($prestamoRepository);
        $prestamos = $listUseCase->executeByTrabajador($trabajadorId);
        
        echo "✓ ÉXITO: Préstamos del trabajador listados\n";
        echo "  └─ Trabajador ID: {$trabajadorId}\n";
        echo "  └─ Total de préstamos: " . count($prestamos) . "\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 8: ACTUALIZAR PRÉSTAMO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 8: ACTUALIZAR PRÉSTAMO\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($prestamoId) {
    try {
        $updateUseCase = new UpdatePrestamoUseCase($prestamoRepository);
        
        $dto = new PrestamoDTO([
            'id' => $prestamoId,
            'observaciones' => 'Préstamo actualizado - Renovado por 15 días más',
            'fecha_devolucion_programada' => date('Y-m-d', strtotime('+45 days'))
        ]);
        
        $result = $updateUseCase->execute($dto);
        
        echo "✓ ÉXITO: Préstamo actualizado\n";
        echo "  └─ ID: {$result->id}\n";
        echo "  └─ Nuevas observaciones: {$result->observaciones}\n";
        echo "  └─ Nueva fecha devolución: {$result->fecha_devolucion_programada}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 9: DEVOLVER PRÉSTAMO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 9: DEVOLVER PRÉSTAMO\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($prestamoId) {
    try {
        $devolverUseCase = new DevolverPrestamoUseCase($prestamoRepository);
        $result = $devolverUseCase->execute($prestamoId, date('Y-m-d'));
        
        echo "✓ ÉXITO: Préstamo devuelto\n";
        echo "  └─ ID: {$prestamoId}\n";
        echo "  └─ Fecha de devolución: " . date('Y-m-d') . "\n\n";
        
        // Verificar el estado
        $getUseCase = new GetPrestamoUseCase($prestamoRepository, $detalleRepository);
        $prestamo = $getUseCase->execute($prestamoId);
        echo "  Verificación:\n";
        echo "  └─ Nuevo estado: {$prestamo->estado}\n";
        echo "  └─ Fecha devolución real: {$prestamo->fecha_devolucion_real}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 10: INTENTAR DEVOLVER PRÉSTAMO YA DEVUELTO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 10: VALIDAR PRÉSTAMO YA DEVUELTO (debe fallar)\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($prestamoId) {
    try {
        $devolverUseCase = new DevolverPrestamoUseCase($prestamoRepository);
        $devolverUseCase->execute($prestamoId, date('Y-m-d'));
        
        echo "✗ FALLO: Debería haber lanzado una excepción\n\n";
        
    } catch (Exception $e) {
        echo "✓ ÉXITO: Validación correcta - {$e->getMessage()}\n\n";
    }
}

// ============================================
// RESUMEN FINAL
// ============================================
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                  PRUEBAS COMPLETADAS                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";