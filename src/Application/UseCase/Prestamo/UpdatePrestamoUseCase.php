<?php
// src/Application/UseCase/Prestamo/UpdatePrestamoUseCase.php
namespace App\Application\UseCase\Prestamo;

use App\Domain\Repository\PrestamoRepositoryInterface;
use App\Application\DTO\PrestamoDTO;

class UpdatePrestamoUseCase
{
    private $prestamoRepository;

    public function __construct(PrestamoRepositoryInterface $prestamoRepository)
    {
        $this->prestamoRepository = $prestamoRepository;
    }

    public function execute(PrestamoDTO $dto)
    {
        if (!$dto->id) {
            throw new \Exception("ID de préstamo requerido para actualizar");
        }

        // Buscar el préstamo existente
        $prestamo = $this->prestamoRepository->getById($dto->id);
        if (!$prestamo) {
            throw new \Exception("Préstamo no encontrado");
        }

        // Validar folio si cambió
        if ($dto->folio && $dto->folio !== $prestamo->getFolio()) {
            $existente = $this->prestamoRepository->findByFolio($dto->folio);
            if ($existente) {
                throw new \Exception("El folio {$dto->folio} ya existe");
            }
        }

        // Actualizar datos
        if ($dto->folio) $prestamo->setFolio($dto->folio);
        if ($dto->trabajador_id) $prestamo->setTrabajadorId($dto->trabajador_id);
        if ($dto->fecha_emision) $prestamo->setFechaEmision($dto->fecha_emision);
        if ($dto->fecha_devolucion_programada) $prestamo->setFechaDevolucionProgramada($dto->fecha_devolucion_programada);
        if ($dto->fecha_devolucion_real !== null) $prestamo->setFechaDevolucionReal($dto->fecha_devolucion_real);
        if ($dto->lugar) $prestamo->setLugar($dto->lugar);
        if ($dto->matricula_autoriza) $prestamo->setMatriculaAutoriza($dto->matricula_autoriza);
        if ($dto->matricula_recibe) $prestamo->setMatriculaRecibe($dto->matricula_recibe);
        if ($dto->estado) $prestamo->setEstado($dto->estado);
        if ($dto->observaciones !== null) $prestamo->setObservaciones($dto->observaciones);

        // Guardar
        $this->prestamoRepository->begin();
        try {
            $this->prestamoRepository->persist($prestamo);
            $this->prestamoRepository->commit();
            return $dto;
        } catch (\Exception $e) {
            throw new \Exception("Error al actualizar préstamo: " . $e->getMessage());
        }
    }
}
