<?php
// src/Application/UseCase/SalidaBien/GetSalidaBienUseCase.php
namespace App\Application\UseCase\SalidaBien;

use App\Domain\Repository\SalidaBienRepositoryInterface;
use App\Domain\Repository\SalidaDetalleRepositoryInterface;
use App\Application\DTO\SalidaBienDTO;

class GetSalidaBienUseCase
{
    private $salidaBienRepository;
    private $salidaDetalleRepository;

    public function __construct(
        SalidaBienRepositoryInterface $salidaBienRepository,
        SalidaDetalleRepositoryInterface $salidaDetalleRepository
    ) {
        $this->salidaBienRepository = $salidaBienRepository;
        $this->salidaDetalleRepository = $salidaDetalleRepository;
    }

    public function execute($id)
    {
        $salidaBien = $this->salidaBienRepository->getById($id);
        
        if (!$salidaBien) {
            throw new \Exception("Salida de bien no encontrada");
        }

        return new SalidaBienDTO([
            'id' => $salidaBien->getId(),
            'folio' => $salidaBien->getFolio(),
            'trabajador_id' => $salidaBien->getTrabajadorId(),
            'area_origen' => $salidaBien->getAreaOrigen(),
            'destino' => $salidaBien->getDestino(),
            'fecha_salida' => $salidaBien->getFechaSalida(),
            'fecha_devolucion_programada' => $salidaBien->getFechaDevolucionProgramada(),
            'sujeto_devolucion' => $salidaBien->getSujetoDevolucion(),
            'lugar' => $salidaBien->getLugar(),
            'observaciones_estado' => $salidaBien->getObservacionesEstado(),
            'estado' => $salidaBien->getEstado()
        ]);
    }

    public function executeByFolio($folio)
    {
        $salidaBien = $this->salidaBienRepository->findByFolio($folio);
        
        if (!$salidaBien) {
            throw new \Exception("Salida de bien con folio {$folio} no encontrada");
        }

        return new SalidaBienDTO([
            'id' => $salidaBien->getId(),
            'folio' => $salidaBien->getFolio(),
            'trabajador_id' => $salidaBien->getTrabajadorId(),
            'area_origen' => $salidaBien->getAreaOrigen(),
            'destino' => $salidaBien->getDestino(),
            'fecha_salida' => $salidaBien->getFechaSalida(),
            'fecha_devolucion_programada' => $salidaBien->getFechaDevolucionProgramada(),
            'sujeto_devolucion' => $salidaBien->getSujetoDevolucion(),
            'lugar' => $salidaBien->getLugar(),
            'observaciones_estado' => $salidaBien->getObservacionesEstado(),
            'estado' => $salidaBien->getEstado()
        ]);
    }

    public function executeWithDetails($id)
    {
        $salidaBien = $this->execute($id);
        $detalles = $this->salidaDetalleRepository->findBySalida($id);
        
        return [
            'salida_bien' => $salidaBien,
            'detalles' => $detalles
        ];
    }
}
