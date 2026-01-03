<?php
// src/Application/UseCase/Bien/CreateBienUseCase.php
namespace App\Application\UseCase\Bien;

use App\Domain\Repository\BienRepositoryInterface;
use App\Domain\Entity\Bien;
use App\Application\DTO\BienDTO;

class CreateBienUseCase
{
    private $bienRepository;

    public function __construct(BienRepositoryInterface $bienRepository)
    {
        $this->bienRepository = $bienRepository;
    }

    public function execute(BienDTO $dto)
    {
        // Validar que la descripción no esté vacía
        if (empty($dto->descripcion)) {
            throw new \Exception("La descripción es obligatoria");
        }

        // Validar que la naturaleza sea válida
        $naturalezasValidas = ['BC', 'BMNC', 'BMC', 'BPS'];
        if (!in_array($dto->naturaleza, $naturalezasValidas)) {
            throw new \Exception("Naturaleza inválida. Debe ser: BC, BMNC, BMC o BPS");
        }

        // Validar que la identificación no exista si se proporciona
        if ($dto->identificacion) {
            $existente = $this->bienRepository->findByIdentificacion($dto->identificacion);
            if ($existente) {
                throw new \Exception("La identificación {$dto->identificacion} ya existe");
            }
        }

        // Crear la entidad
        $bien = new Bien();
        $bien->setIdentificacion($dto->identificacion)
             ->setDescripcion($dto->descripcion)
             ->setMarca($dto->marca)
             ->setModelo($dto->modelo)
             ->setSerie($dto->serie)
             ->setNaturaleza($dto->naturaleza)
             ->setEstadoFisico($dto->estado_fisico);

        // Guardar
        $this->bienRepository->begin();
        try {
            $this->bienRepository->persist($bien);
            $this->bienRepository->commit();
            
            // Retornar DTO con el ID generado
            $dto->id = $bien->getId();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al crear bien: " . $e->getMessage());
        }
    }
}
