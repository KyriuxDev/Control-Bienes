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
        'matricula' => trim($_POST['matricula']),
        'nombre' => trim($_POST['nombre']),
        'institucion' => isset($_POST['institucion']) ? trim($_POST['institucion']) : '',
        'adscripcion' => isset($_POST['adscripcion']) ? trim($_POST['adscripcion']) : '',
        'identificacion' => isset($_POST['identificacion']) ? trim($_POST['identificacion']) : '',
        'telefono' => isset($_POST['telefono']) ? trim($_POST['telefono']) : '',
        'cargo' => trim($_POST['cargo'])
    ]);
    
    $resultado = $useCase->execute($dto);
    
    // Limpiar buffer antes de enviar JSON
    ob_clean();
    
    // Usar propiedades públicas del DTO directamente
    echo json_encode([
        'success' => true,
        'message' => 'Trabajador guardado correctamente',
        'trabajador' => [
            'matricula' => $resultado->matricula,
            'nombre' => $resultado->nombre,
            'institucion' => $resultado->institucion,
            'adscripcion' => $resultado->adscripcion,
            'identificacion' => isset($resultado->identificacion) ? $resultado->identificacion : '',
            'telefono' => $resultado->telefono,
            'cargo' => $resultado->cargo
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