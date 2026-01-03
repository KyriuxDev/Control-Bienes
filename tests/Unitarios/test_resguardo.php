<?php
// test_resguardo.php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLResguardoRepository;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Application\UseCase\Resguardo\CreateResguardoUseCase;
use App\Application\UseCase\Resguardo\GetResguardoUseCase;
use App\Application\UseCase\Resguardo\ListResguardosUseCase;
use App\Application\UseCase\Resguardo\UpdateResguardoUseCase;
use App\Application\UseCase\Resguardo\DevolverResguardoUseCase;
use App\Application\UseCase\Trabajador\CreateTrabajadorUseCase;
use App\Application\UseCase\Bien\CreateBienUseCase;
use App\Application\DTO\ResguardoDTO;
use App\Application\DTO\TrabajadorDTO;
use App\Application\DTO\BienDTO;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║       PRUEBAS DE CASOS DE USO - RESGUARDO                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Obtener conexión a la base de datos
$db = Database::getInstance();
$pdo = $db->getConnection();

// Crear repositorios
$resguardoRepository = new MySQLResguardoRepository($pdo);
$trabajadorRepository = new MySQLTrabajadorRepository($pdo);
$bienRepository = new MySQLBienRepository($pdo);

// Variables para almacenar IDs
$trabajadorId = null;
$bienId = null;
$resguardoId = null;

// ============================================
// PREPARACIÓN: CREAR TRABAJADOR Y BIEN
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PREPARACIÓN: CREAR DATOS DE PRUEBA\n";
echo "═══════════════════════════════════════════════════════════\n";

// Crear trabajador
try {
    $createTrabajador = new CreateTrabajadorUseCase($trabajadorRepository);
    $trabajadorDTO = new TrabajadorDTO([
        'nombre' => 'María González Rodríguez',
        'cargo' => 'Analista de Sistemas',
        'institucion' => 'IMSS',
        'adscripcion' => 'Tecnologías de la Información',
        'matricula' => 'MAT-' . time(),
        'identificacion' => 'CURP987654',
        'direccion' => 'Calle Secundaria #456',
        'telefono' => '5559876543'
    ]);
    
    $trabajador = $createTrabajador->execute($trabajadorDTO);
    $trabajadorId = $trabajador->id;
    echo "✓ Trabajador creado: ID {$trabajadorId} - {$trabajador->nombre}\n";
    
} catch (Exception $e) {
    echo "✗ ERROR al crear trabajador: {$e->getMessage()}\n";
    exit(1);
}

// Crear bien
try {
    $createBien = new CreateBienUseCase($bienRepository);
    $bienDTO = new BienDTO([
        'identificacion' => 'BIEN-' . time(),
        'descripcion' => 'Computadora de Escritorio HP ProDesk',
        'marca' => 'HP',
        'modelo' => 'ProDesk 600 G5',
        'serie' => 'SN' . time(),
        'naturaleza' => 'BC',
        'estado_fisico' => 'Nuevo'
    ]);
    
    $bien = $createBien->execute($bienDTO);
    $bienId = $bien->id;
    echo "✓ Bien creado: ID {$bienId} - {$bien->descripcion}\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR al crear bien: {$e->getMessage()}\n";
    exit(1);
}

// ============================================
// PRUEBA 1: CREAR RESGUARDO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 1: CREAR RESGUARDO\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreateResguardoUseCase(
        $resguardoRepository,
        $trabajadorRepository,
        $bienRepository
    );
    
    $dto = new ResguardoDTO([
        'folio' => 'RES-' . date('Y') . '-' . time(),
        'trabajador_id' => $trabajadorId,
        'bien_id' => $bienId,
        'fecha_asignacion' => date('Y-m-d'),
        'lugar' => 'Oficina 301, Piso 3',
        'estado' => 'ACTIVO',
        'notas_adicionales' => 'Equipo asignado para trabajo permanente'
    ]);
    
    $result = $createUseCase->execute($dto);
    $resguardoId = $result->id;
    
    echo "✓ ÉXITO: Resguardo creado correctamente\n";
    echo "  └─ ID: {$result->id}\n";
    echo "  └─ Folio: {$result->folio}\n";
    echo "  └─ Trabajador ID: {$result->trabajador_id}\n";
    echo "  └─ Bien ID: {$result->bien_id}\n";
    echo "  └─ Fecha asignación: {$result->fecha_asignacion}\n";
    echo "  └─ Lugar: {$result->lugar}\n";
    echo "  └─ Estado: {$result->estado}\n";
    echo "  └─ Notas: {$result->notas_adicionales}\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 2: VALIDAR RESGUARDO SIN TRABAJADOR
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 2: VALIDAR RESGUARDO SIN TRABAJADOR (debe fallar)\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreateResguardoUseCase(
        $resguardoRepository,
        $trabajadorRepository,
        $bienRepository
    );
    
    $dto = new ResguardoDTO([
        'folio' => 'RES-INVALIDO',
        'bien_id' => $bienId,
        'fecha_asignacion' => date('Y-m-d')
    ]);
    
    $result = $createUseCase->execute($dto);
    echo "✗ FALLO: Debería haber lanzado una excepción\n\n";
    
} catch (Exception $e) {
    echo "✓ ÉXITO: Validación correcta - {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 3: VALIDAR RESGUARDO CON TRABAJADOR INEXISTENTE
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 3: VALIDAR TRABAJADOR INEXISTENTE (debe fallar)\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreateResguardoUseCase(
        $resguardoRepository,
        $trabajadorRepository,
        $bienRepository
    );
    
    $dto = new ResguardoDTO([
        'folio' => 'RES-INVALIDO-2',
        'trabajador_id' => 99999, // ID que no existe
        'bien_id' => $bienId,
        'fecha_asignacion' => date('Y-m-d')
    ]);
    
    $result = $createUseCase->execute($dto);
    echo "✗ FALLO: Debería haber lanzado una excepción\n\n";
    
} catch (Exception $e) {
    echo "✓ ÉXITO: Validación correcta - {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 4: OBTENER RESGUARDO POR ID
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 4: OBTENER RESGUARDO POR ID\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($resguardoId) {
    try {
        $getUseCase = new GetResguardoUseCase($resguardoRepository);
        $resguardo = $getUseCase->execute($resguardoId);
        
        echo "✓ ÉXITO: Resguardo encontrado\n";
        echo "  └─ ID: {$resguardo->id}\n";
        echo "  └─ Folio: {$resguardo->folio}\n";
        echo "  └─ Trabajador: {$resguardo->trabajador_id}\n";
        echo "  └─ Bien: {$resguardo->bien_id}\n";
        echo "  └─ Estado: {$resguardo->estado}\n";
        echo "  └─ Lugar: {$resguardo->lugar}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 5: OBTENER RESGUARDO POR FOLIO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 5: OBTENER RESGUARDO POR FOLIO\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($resguardoId) {
    try {
        $getUseCase = new GetResguardoUseCase($resguardoRepository);
        // Primero obtenemos el resguardo para conocer su folio
        $resguardoOriginal = $getUseCase->execute($resguardoId);
        
        // Ahora lo buscamos por folio
        $resguardo = $getUseCase->executeByFolio($resguardoOriginal->folio);
        
        echo "✓ ÉXITO: Resguardo encontrado por folio\n";
        echo "  └─ Folio buscado: {$resguardoOriginal->folio}\n";
        echo "  └─ ID encontrado: {$resguardo->id}\n";
        echo "  └─ Trabajador: {$resguardo->trabajador_id}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 6: LISTAR TODOS LOS RESGUARDOS
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 6: LISTAR TODOS LOS RESGUARDOS\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $listUseCase = new ListResguardosUseCase($resguardoRepository);
    $resguardos = $listUseCase->execute();
    
    echo "✓ ÉXITO: Resguardos listados\n";
    echo "  └─ Total de resguardos: " . count($resguardos) . "\n\n";
    
    if (count($resguardos) > 0) {
        echo "  Últimos 3 resguardos:\n";
        foreach (array_slice($resguardos, -3) as $r) {
            echo "  • Folio: {$r->folio} | Estado: {$r->estado} | Fecha: {$r->fecha_asignacion}\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 7: LISTAR RESGUARDOS ACTIVOS
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 7: LISTAR RESGUARDOS ACTIVOS\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $listUseCase = new ListResguardosUseCase($resguardoRepository);
    $resguardos = $listUseCase->executeActivos();
    
    echo "✓ ÉXITO: Resguardos activos listados\n";
    echo "  └─ Total de resguardos activos: " . count($resguardos) . "\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 8: LISTAR RESGUARDOS POR TRABAJADOR
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 8: LISTAR RESGUARDOS DEL TRABAJADOR\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($trabajadorId) {
    try {
        $listUseCase = new ListResguardosUseCase($resguardoRepository);
        $resguardos = $listUseCase->executeByTrabajador($trabajadorId);
        
        echo "✓ ÉXITO: Resguardos del trabajador listados\n";
        echo "  └─ Trabajador ID: {$trabajadorId}\n";
        echo "  └─ Total de resguardos: " . count($resguardos) . "\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 9: LISTAR RESGUARDOS POR BIEN
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 9: LISTAR RESGUARDOS DEL BIEN\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($bienId) {
    try {
        $listUseCase = new ListResguardosUseCase($resguardoRepository);
        $resguardos = $listUseCase->executeByBien($bienId);
        
        echo "✓ ÉXITO: Resguardos del bien listados\n";
        echo "  └─ Bien ID: {$bienId}\n";
        echo "  └─ Total de resguardos: " . count($resguardos) . "\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 10: ACTUALIZAR RESGUARDO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 10: ACTUALIZAR RESGUARDO\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($resguardoId) {
    try {
        $updateUseCase = new UpdateResguardoUseCase($resguardoRepository);
        
        $dto = new ResguardoDTO([
            'id' => $resguardoId,
            'lugar' => 'Oficina 305, Piso 3 - ACTUALIZADO',
            'notas_adicionales' => 'Cambio de ubicación por remodelación'
        ]);
        
        $result = $updateUseCase->execute($dto);
        
        echo "✓ ÉXITO: Resguardo actualizado\n";
        echo "  └─ ID: {$result->id}\n";
        echo "  └─ Nuevo lugar: {$result->lugar}\n";
        echo "  └─ Nuevas notas: {$result->notas_adicionales}\n\n";
        
        // Verificar actualización
        $getUseCase = new GetResguardoUseCase($resguardoRepository);
        $resguardoActualizado = $getUseCase->execute($resguardoId);
        echo "  Verificación:\n";
        echo "  └─ Lugar en BD: {$resguardoActualizado->lugar}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 11: DEVOLVER RESGUARDO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 11: DEVOLVER RESGUARDO\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($resguardoId) {
    try {
        $devolverUseCase = new DevolverResguardoUseCase($resguardoRepository);
        $result = $devolverUseCase->execute($resguardoId, date('Y-m-d'));
        
        echo "✓ ÉXITO: Resguardo devuelto\n";
        echo "  └─ ID: {$resguardoId}\n";
        echo "  └─ Fecha de devolución: " . date('Y-m-d') . "\n\n";
        
        // Verificar el estado
        $getUseCase = new GetResguardoUseCase($resguardoRepository);
        $resguardo = $getUseCase->execute($resguardoId);
        echo "  Verificación:\n";
        echo "  └─ Nuevo estado: {$resguardo->estado}\n";
        echo "  └─ Fecha devolución: {$resguardo->fecha_devolucion}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 12: INTENTAR DEVOLVER RESGUARDO YA DEVUELTO
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 12: VALIDAR RESGUARDO YA DEVUELTO (debe fallar)\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($resguardoId) {
    try {
        $devolverUseCase = new DevolverResguardoUseCase($resguardoRepository);
        $devolverUseCase->execute($resguardoId, date('Y-m-d'));
        
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