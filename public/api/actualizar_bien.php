<?php
// public/api/actualizar_bien.php - VERSIÓN CORREGIDA CON LOGGING
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

if (ob_get_level()) ob_end_clean();
ob_start();

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Config\Database;
use App\Infrastructure\Repository\MySQLBienRepository;
use App\Application\UseCase\Bien\UpdateBienUseCase;
use App\Application\DTO\BienDTO;

header('Content-Type: application/json; charset=utf-8');

// Log de inicio
error_log("=== ACTUALIZAR BIEN API ===");
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Validar ID - ESTRICTO
    if (!isset($_POST['id_bien']) || $_POST['id_bien'] === '' || $_POST['id_bien'] === null) {
        throw new Exception("El ID del bien es obligatorio para actualizar");
    }

    $idBien = intval($_POST['id_bien']);
    
    if ($idBien <= 0) {
        throw new Exception("El ID del bien no es válido: " . $_POST['id_bien']);
    }
    
    error_log("Actualizando bien con ID: $idBien");

    // Validar descripción
    if (empty($_POST['descripcion']) || trim($_POST['descripcion']) === '') {
        throw new Exception("La descripción es obligatoria");
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $bienRepo = new MySQLBienRepository($pdo);
    
    // Verificar que el bien existe ANTES de intentar actualizar
    $bienExistente = $bienRepo->obtenerPorId($idBien);
    if (!$bienExistente) {
        throw new Exception("No se encontró el bien con ID: $idBien");
    }
    
    error_log("Bien encontrado en BD: " . $bienExistente->getDescripcion());
    
    $useCase = new UpdateBienUseCase($bienRepo);
    
    // Crear DTO con los datos a actualizar
    $dto = new BienDTO([
        'id_bien' => $idBien,
        'descripcion' => trim($_POST['descripcion']),
        'naturaleza' => !empty($_POST['naturaleza']) ? $_POST['naturaleza'] : 'BMNC',
        'marca' => isset($_POST['marca']) ? trim($_POST['marca']) : '',
        'modelo' => isset($_POST['modelo']) ? trim($_POST['modelo']) : '',
        'serie' => isset($_POST['serie']) ? trim($_POST['serie']) : ''
    ]);
    
    error_log("DTO creado con datos: " . print_r($dto->toArray(), true));
    
    $resultado = $useCase->execute($dto);
    
    error_log("Actualización exitosa");
    
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