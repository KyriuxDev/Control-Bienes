<?php
// src/Application/UseCase/Resguardo/UpdateResguardoUseCase.php
namespace App\Application\UseCase\Resguardo;

use App\Domain\Repository\ResguardoRepositoryInterface;
use App\Application\DTO\ResguardoDTO;

class UpdateResguardoUseCase
{
    private $resguardoRepository;

    public function __construct(ResguardoRepositoryInterface $resguardoRepository)
    {
        $this->resguardoRepository = $resguardoRepository;
    }

    public function execute(ResguardoDTO $dto)
    {
        if (!$dto->id) {
            throw new \Exception("ID de resguardo requerido para actualizar");
        }

        // Buscar el resguardo existente
        $resguardo = $this->resguardoRepository->getById($dto->id);
        if (!$resguardo) {
            throw new \Exception("Resguardo no encontrado");
        }

        // Validar folio si cambió
        if ($dto->folio && $dto->folio !== $resguardo->getFolio()) {
            $existente = $this->resguardoRepository->findByFolio($dto->folio);
            if ($existente) {
                throw new \Exception("El folio {$dto->folio} ya existe");
            }
        }

        // Actualizar datos
        if ($dto->folio) $resguardo->setFolio($dto->folio);
        if ($dto->trabajador_id) $resguardo->setTrabajadorId($dto->trabajador_id);
        if ($dto->bien_id) $resguardo->setBienId($dto->bien_id);
        if ($dto->fecha_asignacion) $resguardo->setFechaAsignacion($dto->fecha_asignacion);
        if ($dto->fecha_devolucion !== null) $resguardo->setFechaDevolucion($dto->fecha_devolucion);
        if ($dto->lugar) $resguardo->setLugar($dto->lugar);
        if ($dto->estado) $resguardo->setEstado($dto->estado);
        if ($dto->notas_adicionales !== null) $resguardo->setNotasAdicionales($dto->notas_adicionales);

        // Guardar
        $this->resguardoRepository->begin();
        try {
            $this->resguardoRepository->persist($resguardo);
            $this->resguardoRepository->commit();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar resguardo: " . $e->getMessage());
        }
    }
}
