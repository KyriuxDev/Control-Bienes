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

        // Crear la entidad
        $bien = new Bien();
        $bien->setNaturaleza($dto->naturaleza)
             ->setMarca($dto->marca)
             ->setModelo($dto->modelo)
             ->setSerie($dto->serie)
             ->setDescripcion($dto->descripcion);

        // Guardar
        try {
            $this->bienRepository->persist($bien);
            
            // Retornar DTO con el ID generado
            $dto->id_bien = $bien->getIdBien();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al crear bien: " . $e->getMessage());
        }
    }
}