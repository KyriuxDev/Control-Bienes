<?php
// src/Application/UseCase/Prestamo/ListPrestamosUseCase.php
namespace App\Application\UseCase\Prestamo;

use App\Domain\Repository\PrestamoRepositoryInterface;
use App\Application\DTO\PrestamoDTO;

class ListPrestamosUseCase
{
    private $prestamoRepository;

    public function __construct(PrestamoRepositoryInterface $prestamoRepository)
    {
        $this->prestamoRepository = $prestamoRepository;
    }

    public function execute()
    {
        $prestamos = $this->prestamoRepository->getAll();
        
        return $this->convertToDTO($prestamos);
    }

    public function executeByEstado($estado)
    {
        $prestamos = $this->prestamoRepository->findByEstado($estado);
        
        return $this->convertToDTO($prestamos);
    }

    public function executeByTrabajador($trabajadorId)
    {
        $prestamos = $this->prestamoRepository->findByTrabajador($trabajadorId);
        
        return $this->convertToDTO($prestamos);
    }

    public function executeVencidos()
    {
        $prestamos = $this->prestamoRepository->findVencidos();
        
        return $this->convertToDTO($prestamos);
    }

    private function convertToDTO($prestamos)
    {
        $prestamosDTO = [];
        foreach ($prestamos as $prestamo) {
            $prestamosDTO[] = new PrestamoDTO([
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

        return $prestamosDTO;
    }
}
