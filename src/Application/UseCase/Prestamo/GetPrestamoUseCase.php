<?php
// src/Application/UseCase/Prestamo/GetPrestamoUseCase.php
namespace App\Application\UseCase\Prestamo;

use App\Domain\Repository\PrestamoRepositoryInterface;
use App\Domain\Repository\PrestamoDetalleRepositoryInterface;
use App\Application\DTO\PrestamoDTO;

class GetPrestamoUseCase
{
    private $prestamoRepository;
    private $prestamoDetalleRepository;

    public function __construct(
        PrestamoRepositoryInterface $prestamoRepository,
        PrestamoDetalleRepositoryInterface $prestamoDetalleRepository
    ) {
        $this->prestamoRepository = $prestamoRepository;
        $this->prestamoDetalleRepository = $prestamoDetalleRepository;
    }

    public function execute($id)
    {
        $prestamo = $this->prestamoRepository->getById($id);
        
        if (!$prestamo) {
            throw new \Exception("Préstamo no encontrado");
        }

        return new PrestamoDTO([
            'id' => $prestamo->getId(),
            'folio' => $prestamo->getFolio(),
            'trabajador_id' => $prestamo->getTrabajadorId(),
            'fecha_emision' => $prestamo->getFechaEmision(),
            'fecha_devolucion_programada' => $prestamo->getFechaDevolucionProgramada(),
            'fecha_devolucion_real' => $prestamo->getFechaDevolucionReal(),
            'lugar' => $prestamo->getLugar(),
            'matricula_autoriza' => $prestamo->getMatriculaAutoriza(),
            'matricula_recibe' => $prestamo->getMatriculaRecibe(),
            'estado' => $prestamo->getEstado(),
            'observaciones' => $prestamo->getObservaciones(),
            'fecha_registro' => $prestamo->getFechaRegistro()
        ]);
    }

    public function executeByFolio($folio)
    {
        $prestamo = $this->prestamoRepository->findByFolio($folio);
        
        if (!$prestamo) {
            throw new \Exception("Préstamo con folio {$folio} no encontrado");
        }

        return new PrestamoDTO([
            'id' => $prestamo->getId(),
            'folio' => $prestamo->getFolio(),
            'trabajador_id' => $prestamo->getTrabajadorId(),
            'fecha_emision' => $prestamo->getFechaEmision(),
            'fecha_devolucion_programada' => $prestamo->getFechaDevolucionProgramada(),
            'fecha_devolucion_real' => $prestamo->getFechaDevolucionReal(),
            'lugar' => $prestamo->getLugar(),
            'matricula_autoriza' => $prestamo->getMatriculaAutoriza(),
            'matricula_recibe' => $prestamo->getMatriculaRecibe(),
            'estado' => $prestamo->getEstado(),
            'observaciones' => $prestamo->getObservaciones(),
            'fecha_registro' => $prestamo->getFechaRegistro()
        ]);
    }

    public function executeWithDetails($id)
    {
        $prestamo = $this->execute($id);
        $detalles = $this->prestamoDetalleRepository->findByPrestamo($id);
        
        return [
            'prestamo' => $prestamo,
            'detalles' => $detalles
        ];
    }
}
