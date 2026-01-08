<?php
// src/Application/UseCase/Bien/UpdateBienUseCase.php - VERSIÓN CORREGIDA
namespace App\Application\UseCase\Bien;

use App\Domain\Repository\BienRepositoryInterface;
use App\Application\DTO\BienDTO;

class UpdateBienUseCase
{
    private $bienRepository;

    public function __construct(BienRepositoryInterface $bienRepository)
    {
        $this->bienRepository = $bienRepository;
    }

    public function execute(BienDTO $dto)
    {
        // VALIDACIÓN ESTRICTA: El ID debe existir y no estar vacío
        if (!$dto->id_bien || $dto->id_bien === '' || $dto->id_bien === null) {
            throw new \Exception("ID de bien requerido para actualizar");
        }

        // Buscar el bien existente
        $bien = $this->bienRepository->obtenerPorId($dto->id_bien);
        if (!$bien) {
            throw new \Exception("Bien no encontrado con ID: " . $dto->id_bien);
        }

        // Log para debugging
        error_log("UpdateBienUseCase: Actualizando bien ID " . $dto->id_bien);

        // Validar descripción (es obligatoria)
        if (!$dto->descripcion || trim($dto->descripcion) === '') {
            throw new \Exception("La descripción es obligatoria");
        }

        // Validar naturaleza si se proporciona
        if ($dto->naturaleza) {
            $naturalezasValidas = ['BC', 'BMNC', 'BMC', 'BPS'];
            if (!in_array($dto->naturaleza, $naturalezasValidas)) {
                throw new \Exception("Naturaleza inválida. Debe ser: BC, BMNC, BMC o BPS");
            }
        }

        // Actualizar datos - SIEMPRE actualizar todos los campos
        $bien->setDescripcion(trim($dto->descripcion));
        $bien->setNaturaleza($dto->naturaleza);
        $bien->setMarca($dto->marca ? trim($dto->marca) : '');
        $bien->setModelo($dto->modelo ? trim($dto->modelo) : '');
        $bien->setSerie($dto->serie ? trim($dto->serie) : '');

        // Guardar
        try {
            $this->bienRepository->persist($bien);
            error_log("UpdateBienUseCase: Bien actualizado exitosamente");
            return $dto;
        } catch (\Exception $e) {
            error_log("UpdateBienUseCase ERROR: " . $e->getMessage());
            throw new \Exception("Error al actualizar bien: " . $e->getMessage());
        }
    }
}