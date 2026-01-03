<?php
// test_bien.php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Application\UseCase\Bien\CreateBienUseCase;
use App\Application\UseCase\Bien\GetBienUseCase;
use App\Application\UseCase\Bien\ListBienesUseCase;
use App\Application\UseCase\Bien\UpdateBienUseCase;
use App\Application\UseCase\Bien\DeleteBienUseCase;
use App\Application\DTO\BienDTO;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║          PRUEBAS DE CASOS DE USO - BIEN                    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Obtener conexión a la base de datos
$db = Database::getInstance();
$pdo = $db->getConnection();

// Crear el repositorio
$bienRepository = new MySQLBienRepository($pdo);

// Variable para almacenar el ID del bien creado
$bienId = null;

// ============================================
// PRUEBA 1: CREAR BIEN
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 1: CREAR BIEN\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreateBienUseCase($bienRepository);
    
    $dto = new BienDTO([
        'identificacion' => 'BIEN-' . time(),
        'descripcion' => 'Laptop HP EliteBook 840 G8',
        'marca' => 'HP',
        'modelo' => 'EliteBook 840 G8',
        'serie' => 'SN' . time(),
        'naturaleza' => 'BC',
        'estado_fisico' => 'Nuevo - En perfectas condiciones'
    ]);
    
    $result = $createUseCase->execute($dto);
    $bienId = $result->id;
    
    echo "✓ ÉXITO: Bien creado correctamente\n";
    echo "  └─ ID: {$result->id}\n";
    echo "  └─ Identificación: {$result->identificacion}\n";
    echo "  └─ Descripción: {$result->descripcion}\n";
    echo "  └─ Marca: {$result->marca}\n";
    echo "  └─ Modelo: {$result->modelo}\n";
    echo "  └─ Serie: {$result->serie}\n";
    echo "  └─ Naturaleza: {$result->naturaleza}\n";
    echo "  └─ Estado: {$result->estado_fisico}\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 2: CREAR BIEN CON VALIDACIÓN DE NATURALEZA
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 2: VALIDAR NATURALEZA INCORRECTA (debe fallar)\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreateBienUseCase($bienRepository);
    
    $dto = new BienDTO([
        'identificacion' => 'BIEN-INVALIDO',
        'descripcion' => 'Bien con naturaleza inválida',
        'naturaleza' => 'INVALIDO'
    ]);
    
    $result = $createUseCase->execute($dto);
    echo "✗ FALLO: Debería haber lanzado una excepción\n\n";
    
} catch (Exception $e) {
    echo "✓ ÉXITO: Validación correcta - {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 3: OBTENER BIEN POR ID
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 3: OBTENER BIEN POR ID\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($bienId) {
    try {
        $getUseCase = new GetBienUseCase($bienRepository);
        $bien = $getUseCase->execute($bienId);
        
        echo "✓ ÉXITO: Bien encontrado\n";
        echo "  └─ ID: {$bien->id}\n";
        echo "  └─ Identificación: {$bien->identificacion}\n";
        echo "  └─ Descripción: {$bien->descripcion}\n";
        echo "  └─ Marca: {$bien->marca}\n";
        echo "  └─ Modelo: {$bien->modelo}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 4: LISTAR TODOS LOS BIENES
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 4: LISTAR TODOS LOS BIENES\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $listUseCase = new ListBienesUseCase($bienRepository);
    $bienes = $listUseCase->execute();
    
    echo "✓ ÉXITO: Bienes listados correctamente\n";
    echo "  └─ Total de bienes: " . count($bienes) . "\n\n";
    
    if (count($bienes) > 0) {
        echo "  Primeros 5 bienes:\n";
        foreach (array_slice($bienes, 0, 5) as $bien) {
            echo "  • ID: {$bien->id} | {$bien->identificacion} | {$bien->descripcion}\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 5: LISTAR BIENES POR NATURALEZA
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 5: LISTAR BIENES POR NATURALEZA (BC)\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $listUseCase = new ListBienesUseCase($bienRepository);
    $bienes = $listUseCase->executeByNaturaleza('BC');
    
    echo "✓ ÉXITO: Bienes filtrados por naturaleza BC\n";
    echo "  └─ Total de bienes BC: " . count($bienes) . "\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 6: ACTUALIZAR BIEN
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 6: ACTUALIZAR BIEN\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($bienId) {
    try {
        $updateUseCase = new UpdateBienUseCase($bienRepository);
        
        $dto = new BienDTO([
            'id' => $bienId,
            'descripcion' => 'Laptop HP EliteBook 840 G8 - ACTUALIZADA',
            'estado_fisico' => 'Usado - Buen estado'
        ]);
        
        $result = $updateUseCase->execute($dto);
        
        echo "✓ ÉXITO: Bien actualizado correctamente\n";
        echo "  └─ ID: {$result->id}\n";
        echo "  └─ Nueva descripción: {$result->descripcion}\n";
        echo "  └─ Nuevo estado: {$result->estado_fisico}\n\n";
        
        // Verificar actualización
        $getUseCase = new GetBienUseCase($bienRepository);
        $bienActualizado = $getUseCase->execute($bienId);
        echo "  Verificación:\n";
        echo "  └─ Descripción en BD: {$bienActualizado->descripcion}\n";
        echo "  └─ Estado en BD: {$bienActualizado->estado_fisico}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 7: CREAR VARIOS BIENES DE DIFERENTES NATURALEZAS
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 7: CREAR BIENES DE DIFERENTES NATURALEZAS\n";
echo "═══════════════════════════════════════════════════════════\n";
try {
    $createUseCase = new CreateBienUseCase($bienRepository);
    
    $naturalezas = [
        ['naturaleza' => 'BMNC', 'descripcion' => 'Silla de oficina'],
        ['naturaleza' => 'BMC', 'descripcion' => 'Escritorio ejecutivo'],
        ['naturaleza' => 'BPS', 'descripcion' => 'Software Microsoft Office']
    ];
    
    $creados = 0;
    foreach ($naturalezas as $nat) {
        $dto = new BienDTO([
            'identificacion' => 'BIEN-' . $nat['naturaleza'] . '-' . time() . '-' . rand(1000, 9999),
            'descripcion' => $nat['descripcion'],
            'naturaleza' => $nat['naturaleza'],
            'estado_fisico' => 'Nuevo'
        ]);
        
        $result = $createUseCase->execute($dto);
        echo "✓ Creado: {$nat['naturaleza']} - {$nat['descripcion']} (ID: {$result->id})\n";
        $creados++;
        usleep(100000); // 0.1 segundos entre cada creación
    }
    
    echo "\n✓ ÉXITO: {$creados} bienes creados con diferentes naturalezas\n\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n\n";
}

// ============================================
// PRUEBA 8: OBTENER BIEN POR IDENTIFICACIÓN
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 8: OBTENER BIEN POR IDENTIFICACIÓN\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($bienId) {
    try {
        $getUseCase = new GetBienUseCase($bienRepository);
        // Primero obtenemos el bien para conocer su identificación
        $bienOriginal = $getUseCase->execute($bienId);
        
        // Ahora lo buscamos por identificación
        $bien = $getUseCase->executeByIdentificacion($bienOriginal->identificacion);
        
        echo "✓ ÉXITO: Bien encontrado por identificación\n";
        echo "  └─ Identificación buscada: {$bienOriginal->identificacion}\n";
        echo "  └─ ID encontrado: {$bien->id}\n";
        echo "  └─ Descripción: {$bien->descripcion}\n\n";
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// PRUEBA 9: ELIMINAR BIEN
// ============================================
echo "═══════════════════════════════════════════════════════════\n";
echo "PRUEBA 9: ELIMINAR BIEN\n";
echo "═══════════════════════════════════════════════════════════\n";
if ($bienId) {
    try {
        $deleteUseCase = new DeleteBienUseCase($bienRepository);
        $result = $deleteUseCase->execute($bienId);
        
        echo "✓ ÉXITO: Bien eliminado correctamente\n";
        echo "  └─ ID eliminado: {$bienId}\n\n";
        
        // Verificar que ya no existe
        try {
            $getUseCase = new GetBienUseCase($bienRepository);
            $getUseCase->execute($bienId);
            echo "✗ FALLO: El bien aún existe después de eliminarlo\n\n";
        } catch (Exception $e) {
            echo "✓ Verificación: El bien ya no existe en la BD\n\n";
        }
        
    } catch (Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n\n";
    }
}

// ============================================
// RESUMEN FINAL
// ============================================
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                  PRUEBAS COMPLETADAS                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";