<?php
// public/api/guardar_trabajador.php
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Limpiar buffer
if (ob_get_level()) ob_end_clean();
ob_start();

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Application\UseCase\Trabajador\CreateTrabajadorUseCase;
use App\Application\DTO\TrabajadorDTO;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Validar campos obligatorios
    if (empty($_POST['nombre'])) {
        throw new Exception("El nombre es obligatorio");
    }
    if (empty($_POST['matricula'])) {
        throw new Exception("La matrícula es obligatoria");
    }
    if (empty($_POST['cargo'])) {
        throw new Exception("El cargo es obligatorio");
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $trabajadorRepo = new MySQLTrabajadorRepository($pdo);
    
    $useCase = new CreateTrabajadorUseCase($trabajadorRepo);
    
    $dto = new TrabajadorDTO([
        'nombre' => trim($_POST['nombre']),
        'matricula' => trim($_POST['matricula']),
        'cargo' => trim($_POST['cargo']),
        'institucion' => isset($_POST['institucion']) ? trim($_POST['institucion']) : 'Instituto Mexicano del Seguro Social',
        'adscripcion' => isset($_POST['adscripcion']) ? trim($_POST['adscripcion']) : '',
        'telefono' => isset($_POST['telefono']) ? trim($_POST['telefono']) : '',
        'identificacion' => isset($_POST['identificacion']) ? trim($_POST['identificacion']) : '',
        'direccion' => isset($_POST['direccion']) ? trim($_POST['direccion']) : ''
    ]);
    
    $resultado = $useCase->execute($dto);
    
    // Limpiar buffer antes de enviar JSON
    ob_clean();
    
    // Usar propiedades públicas en lugar de métodos get
    echo json_encode([
        'success' => true,
        'message' => 'Trabajador guardado correctamente',
        'trabajador' => [
            'id' => $resultado->id,
            'nombre' => $resultado->nombre,
            'matricula' => $resultado->matricula,
            'cargo' => $resultado->cargo,
            'institucion' => $resultado->institucion,
            'adscripcion' => $resultado->adscripcion,
            'telefono' => $resultado->telefono,
            'identificacion' => isset($resultado->identificacion) ? $resultado->identificacion : '',
            'direccion' => isset($resultado->direccion) ? $resultado->direccion : ''
        ]
    ]);

} catch (Exception $e) {
    ob_clean();
    error_log("ERROR en guardar_trabajador.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;