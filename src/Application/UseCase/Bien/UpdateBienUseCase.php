<?php
// src/Application/UseCase/Bien/UpdateBienUseCase.php
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
        if (!$dto->id_bien) {
            throw new \Exception("ID de bien requerido para actualizar");
        }

        // Buscar el bien existente
        $bien = $this->bienRepository->obtenerPorId($dto->id_bien);
        if (!$bien) {
            throw new \Exception("Bien no encontrado");
        }

        // Validar naturaleza si se proporciona
        if ($dto->naturaleza) {
            $naturalezasValidas = ['BC', 'BMNC', 'BMC', 'BPS'];
            if (!in_array($dto->naturaleza, $naturalezasValidas)) {
                throw new \Exception("Naturaleza inválida. Debe ser: BC, BMNC, BMC o BPS");
            }
        }

        // Actualizar datos
        if ($dto->naturaleza) $bien->setNaturaleza($dto->naturaleza);
        if ($dto->marca) $bien->setMarca($dto->marca);
        if ($dto->modelo) $bien->setModelo($dto->modelo);
        if ($dto->serie) $bien->setSerie($dto->serie);
        if ($dto->descripcion) $bien->setDescripcion($dto->descripcion);

        // Guardar
        try {
            $this->bienRepository->persist($bien);
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar bien: " . $e->getMessage());
        }
    }
}