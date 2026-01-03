<?php
// src/Application/UseCase/Bien/ListBienesUseCase.php
namespace App\Application\UseCase\Bien;

use App\Domain\Repository\BienRepositoryInterface;
use App\Application\DTO\BienDTO;

class ListBienesUseCase
{
    private $bienRepository;

    public function __construct(BienRepositoryInterface $bienRepository)
    {
        $this->bienRepository = $bienRepository;
    }

    public function execute()
    {
        $bienes = $this->bienRepository->getAll();
        
        $bienesDTO = [];
        foreach ($bienes as $bien) {
            $bienesDTO[] = new BienDTO([
                'id' => $bien->getId(),
                'identificacion' => $bien->getIdentificacion(),
                'descripcion' => $bien->getDescripcion(),
                'marca' => $bien->getMarca(),
                'modelo' => $bien->getModelo(),
                'serie' => $bien->getSerie(),
                'naturaleza' => $bien->getNaturaleza(),
                'estado_fisico' => $bien->getEstadoFisico(),
                'fecha_registro' => $bien->getFechaRegistro()
            ]);
        }

        return $bienesDTO;
    }

    public function executeByNaturaleza($naturaleza)
    {
        $bienes = $this->bienRepository->findByNaturaleza($naturaleza);
        
        $bienesDTO = [];
        foreach ($bienes as $bien) {
            $bienesDTO[] = new BienDTO([
                'id' => $bien->getId(),
                'identificacion' => $bien->getIdentificacion(),
                'descripcion' => $bien->getDescripcion(),
                'marca' => $bien->getMarca(),
                'modelo' => $bien->getModelo(),
                'serie' => $bien->getSerie(),
                'naturaleza' => $bien->getNaturaleza(),
                'estado_fisico' => $bien->getEstadoFisico(),
                'fecha_registro' => $bien->getFechaRegistro()
            ]);
        }

        return $bienesDTO;
    }
}
