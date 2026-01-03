<?php
// test_trabajador.php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Application\UseCase\Trabajador\CreateTrabajadorUseCase;
use App\Application\UseCase\Trabajador\ListTrabajadoresUseCase;
use App\Application\UseCase\Trabajador\GetTrabajadorUseCase;
use App\Application\DTO\TrabajadorDTO;

// Obtener conexión a la base de datos
$db = Database::getInstance();
$pdo = $db->getConnection();

// Crear el repositorio
$trabajadorRepository = new MySQLTrabajadorRepository($pdo);

echo "=== PRUEBA 1: CREAR TRABAJADOR ===\n";
try {
    $createUseCase = new CreateTrabajadorUseCase($trabajadorRepository);
    
    $dto = new TrabajadorDTO([
        'nombre' => 'Juan Pérez',
        'cargo' => 'Desarrollador',
        'institucion' => 'UNAM',
        'adscripcion' => 'Facultad de Ingeniería',
        'matricula' => '12345',
        'identificacion' => 'INE123456',
        'direccion' => 'Calle Falsa 123',
        'telefono' => '5551234567'
    ]);
    
    $result = $createUseCase->execute($dto);
    echo "✓ Trabajador creado con ID: " . $result->id . "\n";
    echo "  Nombre: " . $result->nombre . "\n";
    echo "  Matrícula: " . $result->matricula . "\n\n";
    
    $trabajadorId = $result->id;
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== PRUEBA 2: LISTAR TRABAJADORES ===\n";
try {
    $listUseCase = new ListTrabajadoresUseCase($trabajadorRepository);
    $trabajadores = $listUseCase->execute();
    
    echo "✓ Total de trabajadores: " . count($trabajadores) . "\n";
    foreach ($trabajadores as $t) {
        echo "  - ID: {$t->id}, Nombre: {$t->nombre}, Matrícula: {$t->matricula}\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== PRUEBA 3: OBTENER TRABAJADOR POR ID ===\n";
try {
    if (isset($trabajadorId)) {
        $getUseCase = new GetTrabajadorUseCase($trabajadorRepository);
        $trabajador = $getUseCase->execute($trabajadorId);
        
        echo "✓ Trabajador encontrado:\n";
        echo "  ID: " . $trabajador->id . "\n";
        echo "  Nombre: " . $trabajador->nombre . "\n";
        echo "  Cargo: " . $trabajador->cargo . "\n";
        echo "  Matrícula: " . $trabajador->matricula . "\n\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== PRUEBA 4: OBTENER TRABAJADOR POR MATRÍCULA ===\n";
try {
    $getUseCase = new GetTrabajadorUseCase($trabajadorRepository);
    $trabajador = $getUseCase->executeByMatricula('12345');
    
    echo "✓ Trabajador encontrado por matrícula:\n";
    echo "  ID: " . $trabajador->id . "\n";
    echo "  Nombre: " . $trabajador->nombre . "\n\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== PRUEBAS COMPLETADAS ===\n";