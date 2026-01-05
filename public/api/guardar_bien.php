<?php
// public/api/guardar_bien.php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Application\UseCase\Bien\CreateBienUseCase;
use App\Application\DTO\BienDTO;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $bienRepo = new MySQLBienRepository($pdo);
    
    // Usar el Use Case
    $useCase = new CreateBienUseCase($bienRepo);
    
    $dto = new BienDTO([
        'descripcion' => $_POST['descripcion'],
        'identificacion' => isset($_POST['identificacion']) ? $_POST['identificacion'] : '',
        'naturaleza' => isset($_POST['naturaleza']) ? $_POST['naturaleza'] : 'BMNC',
        'marca' => isset($_POST['marca']) ? $_POST['marca'] : '',
        'modelo' => isset($_POST['modelo']) ? $_POST['modelo'] : '',
        'serie' => isset($_POST['serie']) ? $_POST['serie'] : '',
        'estado_fisico' => isset($_POST['estado_fisico']) ? $_POST['estado_fisico'] : 'BUENO'
    ]);
    
    $resultado = $useCase->execute($dto);

    echo json_encode([
        'success' => true,
        'message' => 'Bien guardado correctamente',
        'bien' => [
            'id' => $resultado->id,
            'identificacion' => $resultado->identificacion,
            'descripcion' => $resultado->descripcion,
            'marca' => $resultado->marca,
            'modelo' => $resultado->modelo
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}