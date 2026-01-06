<?php
// src/Application/UseCase/Resguardo/GetResguardoUseCase.php
namespace App\Application\UseCase\Resguardo;

use App\Domain\Repository\ResguardoRepositoryInterface;
use App\Application\DTO\ResguardoDTO;

class GetResguardoUseCase
{
    private $resguardoRepository;

    public function __construct(ResguardoRepositoryInterface $resguardoRepository)
    {
        $this->resguardoRepository = $resguardoRepository;
    }

    public function execute($id)
    {
        $resguardo = $this->resguardoRepository->getById($id);
        
        if (!$resguardo) {
            throw new \Exception("Resguardo no encontrado");
        }

        return new ResguardoDTO([
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

    public function executeByFolio($folio)
    {
        $resguardo = $this->resguardoRepository->findByFolio($folio);
        
        if (!$resguardo) {
            throw new \Exception("Resguardo con folio {$folio} no encontrado");
        }

        return new ResguardoDTO([
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
}
