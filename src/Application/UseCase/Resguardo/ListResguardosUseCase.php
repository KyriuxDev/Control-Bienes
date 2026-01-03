<?php
// src/Application/UseCase/Resguardo/ListResguardosUseCase.php
namespace App\Application\UseCase\Resguardo;

use App\Domain\Repository\ResguardoRepositoryInterface;
use App\Application\DTO\ResguardoDTO;

class ListResguardosUseCase
{
    private $resguardoRepository;

    public function __construct(ResguardoRepositoryInterface $resguardoRepository)
    {
        $this->resguardoRepository = $resguardoRepository;
    }

    public function execute()
    {
        $resguardos = $this->resguardoRepository->getAll();
        
        return $this->convertToDTO($resguardos);
    }

    public function executeByEstado($estado)
    {
        $resguardos = $this->resguardoRepository->findByEstado($estado);
        
        return $this->convertToDTO($resguardos);
    }

    public function executeByTrabajador($trabajadorId)
    {
        $resguardos = $this->resguardoRepository->findByTrabajador($trabajadorId);
        
        return $this->convertToDTO($resguardos);
    }

    public function executeByBien($bienId)
    {
        $resguardos = $this->resguardoRepository->findByBien($bienId);
        
        return $this->convertToDTO($resguardos);
    }

    public function executeActivos()
    {
        $resguardos = $this->resguardoRepository->findActivos();
        
        return $this->convertToDTO($resguardos);
    }

    private function convertToDTO($resguardos)
    {
        $resguardosDTO = [];
        foreach ($resguardos as $resguardo) {
            $resguardosDTO[] = new ResguardoDTO([
                'id' => $resguardo->getId(),
                'folio' => $resguardo->getFolio(),
                'trabajador_id' => $resguardo->getTrabajadorId(),
                'bien_id' => $resguardo->getBienId(),
                'fecha_asignacion' => $resguardo->getFechaAsignacion(),
                'fecha_devolucion' => $resguardo->getFechaDevolucion(),
                'lugar' => $resguardo->getLugar(),
                'estado' => $resguardo->getEstado(),
                'notas_adicionales' => $resguardo->getNotasAdicionales(),
                'fecha_registro' => $resguardo->getFechaRegistro()
            ]);
        }

        return $resguardosDTO;
    }
}
