<?php
// public/api/guardar_trabajador.php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLTrabajadorRepository;
use App\Application\UseCase\Trabajador\CreateTrabajadorUseCase;
use App\Application\DTO\TrabajadorDTO;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $trabajadorRepo = new MySQLTrabajadorRepository($pdo);
    
    // Usar el Use Case
    $useCase = new CreateTrabajadorUseCase($trabajadorRepo);
    
    $dto = new TrabajadorDTO([
        'nombre' => $_POST['nombre'],
        'matricula' => $_POST['matricula'],
        'cargo' => $_POST['cargo'],
        'institucion' => isset($_POST['institucion']) ? $_POST['institucion'] : 'IMSS',
        'adscripcion' => isset($_POST['adscripcion']) ? $_POST['adscripcion'] : '',
        'telefono' => isset($_POST['telefono']) ? $_POST['telefono'] : '',
        'identificacion' => isset($_POST['identificacion']) ? $_POST['identificacion'] : '',
        'direccion' => isset($_POST['direccion']) ? $_POST['direccion'] : ''
    ]);
    
    $resultado = $useCase->execute($dto);

    echo json_encode([
        'success' => true,
        'message' => 'Trabajador guardado correctamente',
        'trabajador' => [
            'id' => $resultado->id,
            'nombre' => $resultado->nombre,
            'matricula' => $resultado->matricula,
            'cargo' => $resultado->cargo,
            'adscripcion' => $resultado->adscripcion,
            'telefono' => $resultado->telefono
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}