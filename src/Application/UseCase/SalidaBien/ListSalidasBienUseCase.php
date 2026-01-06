<?php
// src/Application/UseCase/SalidaBien/ListSalidasBienUseCase.php
namespace App\Application\UseCase\SalidaBien;

use App\Domain\Repository\SalidaBienRepositoryInterface;
use App\Application\DTO\SalidaBienDTO;

class ListSalidasBienUseCase
{
    private $salidaBienRepository;

    public function __construct(SalidaBienRepositoryInterface $salidaBienRepository)
    {
        $this->salidaBienRepository = $salidaBienRepository;
    }

    public function execute()
    {
        $salidas = $this->salidaBienRepository->getAll();
        
        return $this->convertToDTO($salidas);
    }

    public function executeByEstado($estado)
    {
        $salidas = $this->salidaBienRepository->findByEstado($estado);
        
        return $this->convertToDTO($salidas);
    }

    public function executeByTrabajador($trabajadorId)
    {
        $salidas = $this->salidaBienRepository->findByTrabajador($trabajadorId);
        
        return $this->convertToDTO($salidas);
    }

    public function executeSujetasDevolucion()
    {
        $salidas = $this->salidaBienRepository->findSujetasDevolucion();
        
        return $this->convertToDTO($salidas);
    }

    public function executeEnTransito()
    {
        $salidas = $this->salidaBienRepository->findEnTransito();
        
        return $this->convertToDTO($salidas);
    }

    private function convertToDTO($salidas)
    {
        $salidasDTO = [];
        foreach ($salidas as $salida) {
            $salidasDTO[] = new SalidaBienDTO([
                'id' => $salida->getId(),
                'folio' => $salida->getFolio(),
                'trabajador_id' => $salida->getTrabajadorId(),
                'area_origen' => $salida->getAreaOrigen(),
                'destino' => $salida->getDestino(),
                'fecha_salida' => $salida->getFechaSalida(),
                'fecha_devolucion_programada' => $salida->getFechaDevolucionProgramada(),
                'sujeto_devolucion' => $salida->getSujetoDevolucion(),
                'lugar' => $salida->getLugar(),
                'observaciones_estado' => $salida->getObservacionesEstado(),
                'estado' => $salida->getEstado()
            ]);
        }

        return $salidasDTO;
    }
}
