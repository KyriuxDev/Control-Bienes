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
        if (!$dto->id) {
            throw new \Exception("ID de bien requerido para actualizar");
        }

        // Buscar el bien existente
        $bien = $this->bienRepository->getById($dto->id);
        if (!$bien) {
            throw new \Exception("Bien no encontrado");
        }

        // Validar identificación si cambió
        if ($dto->identificacion && $dto->identificacion !== $bien->getIdentificacion()) {
            $existente = $this->bienRepository->findByIdentificacion($dto->identificacion);
            if ($existente) {
                throw new \Exception("La identificación {$dto->identificacion} ya existe");
            }
        }

        // Validar naturaleza si se proporciona
        if ($dto->naturaleza) {
            $naturalezasValidas = ['BC', 'BMNC', 'BMC', 'BPS'];
            if (!in_array($dto->naturaleza, $naturalezasValidas)) {
                throw new \Exception("Naturaleza inválida. Debe ser: BC, BMNC, BMC o BPS");
            }
        }

        // Actualizar datos
        if ($dto->identificacion) $bien->setIdentificacion($dto->identificacion);
        if ($dto->descripcion) $bien->setDescripcion($dto->descripcion);
        if ($dto->marca) $bien->setMarca($dto->marca);
        if ($dto->modelo) $bien->setModelo($dto->modelo);
        if ($dto->serie) $bien->setSerie($dto->serie);
        if ($dto->naturaleza) $bien->setNaturaleza($dto->naturaleza);
        if ($dto->estado_fisico) $bien->setEstadoFisico($dto->estado_fisico);

        // Guardar
        $this->bienRepository->begin();
        try {
            $this->bienRepository->persist($bien);
            $this->bienRepository->commit();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar bien: " . $e->getMessage());
        }
    }
}
