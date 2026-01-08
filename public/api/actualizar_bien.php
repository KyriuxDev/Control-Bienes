<?php
// public/api/actualizar_bien.php
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (ob_get_level()) ob_end_clean();
ob_start();

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Application\UseCase\Bien\UpdateBienUseCase;
use App\Application\DTO\BienDTO;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Validar ID
    if (empty($_POST['id_bien'])) {
        throw new Exception("El ID del bien es obligatorio");
    }

    // Validar descripción
    if (empty($_POST['descripcion'])) {
        throw new Exception("La descripción es obligatoria");
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $bienRepo = new MySQLBienRepository($pdo);
    
    $useCase = new UpdateBienUseCase($bienRepo);
    
    // Crear DTO con los datos a actualizar
    $dto = new BienDTO([
        'id_bien' => intval($_POST['id_bien']),
        'descripcion' => trim($_POST['descripcion']),
        'naturaleza' => !empty($_POST['naturaleza']) ? $_POST['naturaleza'] : 'BMNC',
        'marca' => isset($_POST['marca']) ? trim($_POST['marca']) : '',
        'modelo' => isset($_POST['modelo']) ? trim($_POST['modelo']) : '',
        'serie' => isset($_POST['serie']) ? trim($_POST['serie']) : ''
    ]);
    
    $resultado = $useCase->execute($dto);
    
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'message' => 'Bien actualizado correctamente',
        'bien' => [
            'id' => $resultado->id_bien,
            'descripcion' => $resultado->descripcion,
            'marca' => $resultado->marca,
            'modelo' => $resultado->modelo,
            'serie' => $resultado->serie,
            'naturaleza' => $resultado->naturaleza
        ]
    ]);

} catch (Exception $e) {
    ob_clean();
    error_log("ERROR en actualizar_bien.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;